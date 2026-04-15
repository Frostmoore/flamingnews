<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRules;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private const VALID_CATEGORIES = [
        'politica', 'economia', 'esteri', 'tecnologia', 'sport', 'cultura',
        'generale', 'scienza', 'salute', 'ambiente', 'istruzione', 'cibo', 'viaggi',
    ];

    // -------------------------------------------------------------------------
    // Registrazione email/password
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // Gate registrazioni
    // -------------------------------------------------------------------------

    private function registrationsClosed(): bool
    {
        return (bool) config('flamingnews.registrations_closed');
    }

    // -------------------------------------------------------------------------
    // Registrazione email/password
    // -------------------------------------------------------------------------

    public function register(Request $request): JsonResponse
    {
        if ($this->registrationsClosed()) {
            return response()->json(['message' => 'Le registrazioni sono temporaneamente chiuse.'], 403);
        }

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'username'              => 'required|string|alpha_dash|min:3|max:30|unique:users,username',
            'email'                 => 'required|email|unique:users,email',
            'password'              => ['required', 'confirmed', PasswordRules::min(8)->mixedCase()->numbers()->symbols()],
            'preferred_categories'  => 'nullable|array',
            'preferred_categories.*'=> 'in:' . implode(',', self::VALID_CATEGORIES),
            'preferred_sources'     => 'nullable|array',
            'preferred_sources.*'   => 'string|max:100',
        ]);

        $user  = User::create($validated);
        $user->sendEmailVerificationNotification();
        $token = $user->createToken('flamingnews')->plainTextToken;

        return response()->json([
            'user'           => $this->formatUser($user),
            'token'          => $token,
            'email_verified' => false,
        ], 201);
    }

    // -------------------------------------------------------------------------
    // Login email/password
    // -------------------------------------------------------------------------

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->login;
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user  = User::where($field, $login)->first();

        if (!$user || !$user->password || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenziali non valide.'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return response()->json([
                'message' => 'Devi verificare la tua email prima di accedere. Ti abbiamo reinviato l\'email di verifica.',
            ], 403);
        }

        $token = $user->createToken('flamingnews')->plainTextToken;

        return response()->json([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout effettuato.']);
    }

    // -------------------------------------------------------------------------
    // Aggiorna categorie preferite
    // -------------------------------------------------------------------------

    public function updateCategories(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preferred_categories'   => 'required|array|min:1',
            'preferred_categories.*' => 'in:' . implode(',', self::VALID_CATEGORIES),
        ]);

        $request->user()->update(['preferred_categories' => $validated['preferred_categories']]);

        return response()->json([
            'user' => $this->formatUser($request->user()->fresh()),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(['email' => $request->email]);

        // Rispondiamo sempre con 200 per non rivelare se l'email esiste
        return response()->json(['message' => 'Se l\'indirizzo è associato a un account, riceverai un\'email con il link di ripristino.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', PasswordRules::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
                $user->tokens()->delete(); // invalida tutti i token esistenti
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reimpostata con successo. Ora puoi accedere.']);
        }

        return response()->json(['message' => 'Link non valido o scaduto. Richiedi un nuovo link.'], 422);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|alpha_dash|min:3|max:30|unique:users,username,' . $user->id,
            'email'    => 'required|email|unique:users,email,' . $user->id,
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        if ($emailChanged) {
            $validated['email_verified_at'] = null;
        }

        $user->update($validated);
        $user->refresh();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'user'          => $this->formatUser($user),
            'email_changed' => $emailChanged,
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', PasswordRules::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        if (!$user->password || !Hash::check($request->current_password, $user->password)) {
            return response()->json(['errors' => ['current_password' => ['La password attuale non è corretta.']]], 422);
        }

        $user->update(['password' => $request->password]);

        return response()->json(['message' => 'Password aggiornata con successo.']);
    }

    public function updateSources(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preferred_sources'   => 'required|array|min:1',
            'preferred_sources.*' => 'string|max:100',
        ]);

        $request->user()->update(['preferred_sources' => $validated['preferred_sources']]);

        return response()->json([
            'user' => $this->formatUser($request->user()->fresh()),
        ]);
    }

    // -------------------------------------------------------------------------
    // Google OAuth — redirect al consent screen
    // -------------------------------------------------------------------------

    public function googleRedirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    // -------------------------------------------------------------------------
    // Google OAuth — callback
    // -------------------------------------------------------------------------

    public function googleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            // Redirect al frontend con errore
            return redirect(config('app.url') . '/login?error=google_failed');
        }

        $existing = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (!$existing && $this->registrationsClosed()) {
            return redirect(config('app.url') . '/login?error=registration_closed');
        }

        $user = User::updateOrCreate(
            ['google_id' => $googleUser->getId()],
            [
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'avatar'            => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]
        );

        // Se l'utente esiste già per email ma non aveva google_id
        if (!$user->wasRecentlyCreated && !$user->google_id) {
            $user->update(['google_id' => $googleUser->getId(), 'avatar' => $googleUser->getAvatar()]);
        }

        $token = $user->createToken('flamingnews-google')->plainTextToken;

        // Redirect al frontend con token — il JS lo salva in localStorage
        $frontendUrl = config('app.url');
        $needsCategories = empty($user->preferred_categories) ? 'true' : 'false';

        return redirect("{$frontendUrl}/auth/callback?token={$token}&needs_categories={$needsCategories}");
    }

    // -------------------------------------------------------------------------
    // Token OAuth per mobile (Google ID token → Sanctum token)
    // -------------------------------------------------------------------------

    public function googleMobile(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            // Verifica il token Google tramite Socialite stateless
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->id_token);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Token Google non valido.'], 401);
        }

        $existing = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (!$existing && $this->registrationsClosed()) {
            return response()->json(['message' => 'Le registrazioni sono temporaneamente chiuse.'], 403);
        }

        $user = User::updateOrCreate(
            ['google_id' => $googleUser->getId()],
            [
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'avatar'            => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]
        );

        $token = $user->createToken('flamingnews-mobile')->plainTextToken;

        return response()->json([
            'user'            => $this->formatUser($user),
            'token'           => $token,
            'needs_categories'=> empty($user->preferred_categories),
        ]);
    }

    // -------------------------------------------------------------------------
    // Utente corrente
    // -------------------------------------------------------------------------

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->formatUser($request->user())]);
    }

    // -------------------------------------------------------------------------
    // Formato utente condiviso
    // -------------------------------------------------------------------------

    private function formatUser(User $user): array
    {
        return [
            'id'                    => $user->id,
            'name'                  => $user->name,
            'username'              => $user->username,
            'email'                 => $user->email,
            'avatar'                => $user->avatar,
            'is_premium'            => $user->is_premium,
            'email_verified'        => $user->hasVerifiedEmail(),
            'preferred_categories'  => $user->preferred_categories ?? [],
            'preferred_sources'     => $user->preferred_sources ?? [],
            'has_google'            => !is_null($user->google_id),
        ];
    }
}
