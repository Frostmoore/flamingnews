<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
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
            'email'                 => 'required|email|unique:users,email',
            'password'              => ['required', 'confirmed', Password::min(8)],
            'preferred_categories'  => 'nullable|array',
            'preferred_categories.*'=> 'in:' . implode(',', self::VALID_CATEGORIES),
        ]);

        $user  = User::create($validated);
        $token = $user->createToken('flamingnews')->plainTextToken;

        return response()->json([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ], 201);
    }

    // -------------------------------------------------------------------------
    // Login email/password
    // -------------------------------------------------------------------------

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->password || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenziali non valide.'], 401);
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
            'email'                 => $user->email,
            'avatar'                => $user->avatar,
            'is_premium'            => $user->is_premium,
            'preferred_categories'  => $user->preferred_categories ?? [],
            'has_google'            => !is_null($user->google_id),
        ];
    }
}
