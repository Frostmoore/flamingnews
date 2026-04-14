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

      <!-- Errore -->
      <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded" x-text="error"></div>

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

      <!-- Form email/password -->
      <form @submit.prevent="login">
        <div class="mb-4">
          <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Email</label>
          <input type="email" x-model="email" required autocomplete="email"
            class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A] transition-colors"
            placeholder="tu@esempio.it" />
        </div>
        <div class="mb-6">
          <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Password</label>
          <input type="password" x-model="password" required autocomplete="current-password"
            class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A] transition-colors"
            placeholder="••••••••" />
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
    email: '', password: '', loading: false, error: '',
    async login() {
      this.loading = true; this.error = '';
      try {
        const res = await fetch('/api/auth/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ email: this.email, password: this.password }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Credenziali non valide.');
        localStorage.setItem('fn_token', data.token);
        localStorage.setItem('fn_user', JSON.stringify(data.user));
        // Redirect: se non ha categorie, vai alla selezione
        window.location.href = (data.user.preferred_categories?.length === 0) ? '/auth/categories' : '/';
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
