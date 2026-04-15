@extends('layouts.app')
@section('title', 'Il mio profilo')
@section('requires_auth', true)

@section('content')
<div class="min-h-screen bg-[#F8F6F1]" x-data="profilePage()" x-init="init()">

  <!-- Header -->
  <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
    <div class="max-w-2xl mx-auto px-4 h-14 flex items-center gap-4">
      <a href="/" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <span class="font-bold text-[#1A1A1A]">Il mio profilo</span>
    </div>
  </header>

  <div class="max-w-2xl mx-auto px-4 py-8 space-y-4">

    <!-- Avatar + nome -->
    <div class="bg-white border border-gray-200 p-6 flex items-center gap-4">
      <div class="w-14 h-14 rounded-full bg-[#C41E3A] flex items-center justify-center text-white text-xl font-bold flex-shrink-0"
           x-text="user.name ? user.name[0].toUpperCase() : '?'"></div>
      <div>
        <div class="font-bold text-lg text-[#1A1A1A]" x-text="user.name"></div>
        <div class="text-sm text-gray-400" x-text="user.username ? '@' + user.username : ''"></div>
        <div class="mt-1 flex items-center gap-1.5">
          <template x-if="user.email_verified">
            <span class="text-[11px] font-semibold text-green-600 bg-green-50 border border-green-200 px-2 py-0.5 rounded-full">Email verificata</span>
          </template>
          <template x-if="!user.email_verified">
            <span class="text-[11px] font-semibold text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">Email non verificata</span>
          </template>
        </div>
      </div>
    </div>

    <!-- ── Sezione: Dati personali ─────────────────────────────────────── -->
    <div class="bg-white border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-bold text-[#1A1A1A]">Dati personali</h2>
      </div>
      <form @submit.prevent="saveProfile" class="px-6 py-5 space-y-4">

        <div x-show="profileSuccess" x-cloak class="p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          <span x-text="profileSuccess"></span>
        </div>
        <div x-show="profileError" x-cloak class="p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded" x-text="profileError"></div>

        <!-- Nome -->
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nome</label>
          <input type="text" x-model="form.name" required autocomplete="name"
            class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A] transition-colors" />
        </div>

        <!-- Username -->
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Username</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">@</span>
            <input type="text" x-model="form.username" required autocomplete="username"
              pattern="[a-zA-Z0-9_\-]+"
              class="w-full border border-gray-300 pl-7 pr-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A] transition-colors" />
          </div>
          <p class="text-[11px] text-gray-400 mt-1">Solo lettere, numeri, trattini e underscore (3–30 caratteri).</p>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Email</label>
          <input type="email" x-model="form.email" required autocomplete="email"
            class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A] transition-colors" />
          <p class="text-[11px] text-amber-500 mt-1" x-show="form.email !== user.email">
            Cambiando l'email dovrai verificarla di nuovo.
          </p>
        </div>

        <button type="submit" :disabled="profileLoading"
          class="w-full bg-[#C41E3A] text-white py-2.5 text-sm font-bold tracking-wide hover:bg-red-800 transition-colors disabled:opacity-50">
          <span x-show="!profileLoading">Salva modifiche</span>
          <span x-show="profileLoading">Salvataggio...</span>
        </button>
      </form>
    </div>

    <!-- ── Sezione: Password ──────────────────────────────────────────── -->
    <div class="bg-white border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-bold text-[#1A1A1A]">Password</h2>
      </div>
      <div class="px-6 py-5">
        <p class="text-sm text-gray-500 mb-4 leading-relaxed">
          Ti invieremo un link via email per impostare una nuova password sicura.
        </p>
        <div x-show="resetSuccess" x-cloak class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          Email inviata! Controlla la tua casella di posta.
        </div>
        <div x-show="resetError" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded" x-text="resetError"></div>
        <button @click="sendPasswordReset" :disabled="resetLoading"
          class="w-full border border-[#C41E3A] text-[#C41E3A] py-2.5 text-sm font-bold tracking-wide hover:bg-red-50 transition-colors disabled:opacity-50">
          <span x-show="!resetLoading">Invia email di ripristino</span>
          <span x-show="resetLoading">Invio in corso...</span>
        </button>
      </div>
    </div>

    <!-- ── Logout ──────────────────────────────────────────────────────── -->
    <div class="bg-white border border-gray-200 p-4">
      <button @click="logout"
        class="w-full text-[#C41E3A] font-bold text-sm py-2 hover:bg-red-50 transition-colors border border-[#C41E3A]">
        Esci dall'account
      </button>
    </div>

  </div>
</div>

<script>
function profilePage() {
  return {
    user: {},
    form: { name: '', username: '', email: '' },
    profileLoading: false, profileError: '', profileSuccess: '',
    resetLoading: false, resetError: '', resetSuccess: false,

    init() {
      const raw = localStorage.getItem('fn_user');
      if (!raw) { window.location.href = '/login'; return; }
      this.user = JSON.parse(raw);
      this.form.name     = this.user.name     ?? '';
      this.form.username = this.user.username  ?? '';
      this.form.email    = this.user.email     ?? '';
    },

    token() { return localStorage.getItem('fn_token'); },

    async saveProfile() {
      this.profileLoading = true; this.profileError = ''; this.profileSuccess = '';
      try {
        const res = await fetch('/api/auth/profile', {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer ' + this.token() },
          body: JSON.stringify(this.form),
        });
        const data = await res.json();
        if (!res.ok) {
          const msgs = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
          throw new Error(msgs || 'Errore nel salvataggio.');
        }
        this.user = data.user;
        localStorage.setItem('fn_user', JSON.stringify(data.user));
        this.profileSuccess = data.email_changed
          ? 'Profilo salvato. Controlla la tua nuova email per verificarla.'
          : 'Profilo aggiornato con successo.';
        setTimeout(() => this.profileSuccess = '', 5000);
      } catch (e) {
        this.profileError = e.message;
      } finally {
        this.profileLoading = false;
      }
    },

    async sendPasswordReset() {
      this.resetLoading = true; this.resetError = ''; this.resetSuccess = false;
      try {
        const res = await fetch('/api/auth/forgot-password', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ email: this.user.email }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Errore nell\'invio.');
        this.resetSuccess = true;
        setTimeout(() => this.resetSuccess = false, 8000);
      } catch (e) {
        this.resetError = e.message;
      } finally {
        this.resetLoading = false;
      }
    },

    async logout() {
      await fetch('/api/auth/logout', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + this.token(), 'Accept': 'application/json' },
      });
      localStorage.removeItem('fn_token');
      localStorage.removeItem('fn_user');
      window.location.href = '/login';
    },
  };
}
</script>
@endsection
