<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (!URL::hasValidSignature($request)) {
            return view('auth.email-invalid');
        }

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Hash non valido.');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect('/login?verified=1');
    }
}
