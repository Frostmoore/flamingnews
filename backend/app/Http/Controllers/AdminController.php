<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Source;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // ── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $stats = [
            'users'        => User::count(),
            'admins'       => User::where('is_admin', true)->count(),
            'sources'      => Source::count(),
            'sources_active' => Source::where('active', true)->count(),
            'articles'     => Article::count(),
            'topics'       => Article::whereNotNull('topic_id')->distinct('topic_id')->count('topic_id'),
            'feeds'        => Source::whereNotNull('feed_url')->where('active', true)->count(),
            'user_feeds'   => DB::table('user_feeds')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    // ── Utenti ───────────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        $q = $request->input('q');

        $users = User::when($q, fn ($query) =>
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('username', 'like', "%{$q}%")
        )
        ->orderByDesc('created_at')
        ->paginate(30)
        ->withQueryString();

        return view('admin.users', compact('users', 'q'));
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'email_verified' => 'sometimes|boolean',
            'is_premium'     => 'sometimes|boolean',
        ]);

        if (array_key_exists('email_verified', $data)) {
            $user->email_verified_at = $data['email_verified'] ? now() : null;
        }

        if (array_key_exists('is_premium', $data)) {
            $user->is_premium = $data['is_premium'];
        }

        $user->save();

        return response()->json(['ok' => true]);
    }

    public function deleteUser(User $user)
    {
        if ($user->is_admin) {
            return response()->json(['error' => 'Non puoi eliminare un amministratore.'], 422);
        }

        $user->delete();
        return response()->json(['ok' => true]);
    }

    // ── Testate (Sources) ────────────────────────────────────────────────────

    public function sources(Request $request)
    {
        $q    = $request->input('q');
        $lean = $request->input('lean');

        $sources = Source::withCount('articles')
            ->when($q, fn ($query) =>
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('domain', 'like', "%{$q}%")
            )
            ->when($lean, fn ($query) => $query->where('political_lean', $lean))
            ->orderBy('name')
            ->paginate(40)
            ->withQueryString();

        $leans = ['left','center-left','center','center-right','right','international','altro'];

        return view('admin.sources', compact('sources', 'q', 'lean', 'leans'));
    }

    public function updateSource(Request $request, Source $source)
    {
        $data = $request->validate([
            'name'          => 'sometimes|string|max:100',
            'political_lean'=> ['sometimes', Rule::in(['left','center-left','center','center-right','right','international','altro'])],
            'tier'          => 'sometimes|integer|in:1,2',
            'active'        => 'sometimes|boolean',
            'feed_url'      => 'sometimes|nullable|url|max:500',
        ]);

        $source->update($data);
        return response()->json(['ok' => true]);
    }

    public function createSource(Request $request)
    {
        $data = $request->validate([
            'domain'        => 'required|string|max:100|unique:sources,domain',
            'name'          => 'required|string|max:100',
            'political_lean'=> ['required', Rule::in(['left','center-left','center','center-right','right','international','altro'])],
            'tier'          => 'required|integer|in:1,2',
            'feed_url'      => 'nullable|url|max:500',
        ]);

        $source = Source::create(array_merge($data, ['active' => true]));
        return response()->json(['ok' => true, 'id' => $source->id]);
    }

    public function deleteSource(Source $source)
    {
        $source->delete();
        return response()->json(['ok' => true]);
    }

    // ── Fetch manuale ─────────────────────────────────────────────────────────

    public function triggerFetch()
    {
        \App\Jobs\FetchNewsJob::dispatch();
        return response()->json(['ok' => true, 'message' => 'Job avviato.']);
    }
}
