@extends('layouts.app')
@section('title', 'Accedi')

@section('content')
<div class="min-h-screen bg-[#F8F6F1] flex items-center justify-center p-4">
  <div class="w-full max-w-sm">

    <a href="/" class="block font-display text-3xl font-bold text-[#C41E3A] text-center mb-8 tracking-tight">
      FlamingNews
    </a>

    <div class="bg-white border border-gray-200 p-8 shadow-sm" x-data="loginForm()">

      <h1 class="text-xl font-bold text-[#1A1A1A] mb-6">Accedi al tuo account</h1>

      <!-- Messaggio verifica completata -->
      <div x-show="verified" x-cloak class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Email verificata con successo! Ora puoi accedere.
      </div>

      <!-- Errore / avviso verifica richiesta -->
      <div x-show="error" x-cloak class="mb-4 p-3 text-sm rounded" :class="needsVerification ? 'bg-amber-50 border border-amber-200 text-amber-700' : 'bg-red-50 border border-red-200 text-red-700'" x-text="error"></div>

      <!-- Bottone Google -->
      <a
        href="/api/auth/google/redirect"
        class="flex items-center justify-center gap-3 w-full border border-gray-300 bg-white text-[#1A1A1A] py-2.5 text-sm font-semibold rounded hover:bg-gray-50 transition-colors mb-4"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Accedi con Google
      </a>

      <div class="flex items-center gap-3 mb-4">
        <div class="flex-1 h-px bg-gray-200"></div>
        <span class="text-xs text-gray-400 uppercase tracking-wider">oppure</span>
        <div class="flex-1 h-px bg-gray-200"></div>
      </div>

      <!-- Form login/password -->
      <form @submit.prevent="login">
        <div class="mb-4">
          <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Email o Username</label>
          <input type="text" x-model="loginField" required autocomplete="username"
            class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A] transition-colors"
            placeholder="tu@esempio.it oppure mario_rossi" />
        </div>
        <div class="mb-6">
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Password</label>
            <a href="/forgot-password" class="text-xs text-[#C41E3A] hover:underline font-semibold">Password dimenticata?</a>
          </div>
          <div class="relative">
            <input :type="showPass ? 'text' : 'password'" x-model="password" required autocomplete="current-password"
              class="w-full border border-gray-300 px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-[#C41E3A] transition-colors"
              placeholder="••••••••" />
            <button type="button" @click="showPass = !showPass"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
              <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
              </svg>
            </button>
          </div>
        </div>
        <button type="submit" :disabled="loading"
          class="w-full bg-[#C41E3A] text-white py-2.5 text-sm font-bold tracking-wide hover:bg-red-800 transition-colors disabled:opacity-50">
          <span x-show="!loading">Accedi</span>
          <span x-show="loading">Accesso in corso...</span>
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-5">
        Non hai un account?
        <a href="/register" class="text-[#C41E3A] font-bold hover:underline">Registrati</a>
      </p>
    </div>
  </div>
</div>

<script>
function loginForm() {
  return {
    loginField: '', password: '', showPass: false, loading: false, error: '',
    verified: new URLSearchParams(location.search).get('verified') === '1',
    needsVerification: false,
    async login() {
      this.loading = true; this.error = ''; this.needsVerification = false;
      try {
        const res = await fetch('/api/auth/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ login: this.loginField, password: this.password }),
        });
        const data = await res.json();
        if (!res.ok) {
          if (res.status === 403) this.needsVerification = true;
          throw new Error(data.message || 'Credenziali non valide.');
        }
        localStorage.setItem('fn_token', data.token);
        localStorage.setItem('fn_user', JSON.stringify(data.user));
        window.location.href = (data.user.preferred_sources?.length === 0) ? '/auth/categories' : '/';
      } catch (e) {
        this.error = e.message;
      } finally {
        this.loading = false;
      }
    },
  };
}
</script>
@endsection
