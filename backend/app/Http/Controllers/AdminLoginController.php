<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminLoginController extends Controller
{
    public function showLogin()
    {
        if (session('admin_auth')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $correct = Hash::check($request->password, config('admin.password_hash'))
            || $request->password === config('admin.password');

        if (! $correct) {
            return back()->withErrors(['password' => 'Password errata.']);
        }

        session(['admin_auth' => true]);
        session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        session()->forget('admin_auth');
        return redirect('/');
    }
}
