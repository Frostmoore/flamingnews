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

    <!-- ── Sezione: Feed RSS personali ─────────────────────────────────── -->
    <div class="bg-white border border-gray-200" x-data="feedsSection()">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-bold text-[#1A1A1A]">Feed RSS personali</h2>
        <button @click="showAdd = !showAdd"
          class="text-xs font-bold text-[#C41E3A] border border-[#C41E3A] px-3 py-1 hover:bg-red-50 transition-colors">
          + Aggiungi
        </button>
      </div>

      <!-- Form aggiunta -->
      <div x-show="showAdd" x-cloak class="px-6 py-4 border-b border-gray-100 bg-gray-50">
        <div x-show="addError" x-cloak class="mb-3 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded" x-text="addError"></div>
        <div class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Nome feed</label>
            <input type="text" x-model="newName" placeholder="Es. Il mio blog preferito"
              class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#C41E3A] transition-colors" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">URL feed RSS</label>
            <input type="url" x-model="newUrl" placeholder="https://esempio.it/feed"
              class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#C41E3A] transition-colors" />
          </div>
          <div class="flex gap-2">
            <button @click="addFeed" :disabled="addLoading"
              class="px-4 py-2 bg-[#C41E3A] text-white text-sm font-bold hover:bg-red-800 transition-colors disabled:opacity-50">
              <span x-show="!addLoading">Aggiungi feed</span>
              <span x-show="addLoading">Verifica in corso...</span>
            </button>
            <button @click="showAdd = false; addError = ''"
              class="px-4 py-2 border border-gray-300 text-sm text-gray-600 hover:border-gray-400 transition-colors">
              Annulla
            </button>
          </div>
        </div>
      </div>

      <!-- Lista feed -->
      <div class="divide-y divide-gray-100">
        <template x-if="feeds.length === 0">
          <div class="px-6 py-8 text-center text-sm text-gray-400">
            Nessun feed aggiunto. Aggiungi un feed RSS per leggerlo qui.
          </div>
        </template>
        <template x-for="feed in feeds" :key="feed.id">
          <div class="px-6 py-4">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="font-semibold text-sm text-[#1A1A1A] truncate" x-text="feed.name"></div>
                <div class="text-xs text-gray-400 truncate mt-0.5" x-text="feed.feed_url"></div>
                <div class="text-xs text-gray-400 mt-1">
                  <span x-text="feed.articles_count"></span> articoli
                  <span x-show="feed.last_fetched_at"> · aggiornato <span x-text="timeAgo(feed.last_fetched_at)"></span></span>
                </div>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <a :href="'/my-feeds/' + feed.id" class="text-xs font-semibold text-[#C41E3A] hover:underline">Leggi</a>
                <button @click="refreshFeed(feed)" :disabled="feed.refreshing"
                  class="text-xs text-gray-400 hover:text-[#1A1A1A] transition-colors" title="Aggiorna">
                  <svg class="w-4 h-4" :class="{'animate-spin': feed.refreshing}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M20 20v-5h-.581M5.635 15A9 9 0 1 0 4.582 9"/>
                  </svg>
                </button>
                <button @click="deleteFeed(feed.id)"
                  class="text-xs text-red-400 hover:text-red-700 transition-colors" title="Rimuovi">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- ── Admin (solo smp-webmaster) ───────────────────────────────── -->
    <template x-if="user.username === 'smp-webmaster'">
      <div class="bg-white border border-gray-200 p-4">
        <a href="/admin"
           class="flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-[#C41E3A] transition-colors">
          <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Amministrazione
        </a>
      </div>
    </template>

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
function feedsSection() {
  return {
    feeds: [],
    showAdd: false,
    newName: '', newUrl: '',
    addLoading: false, addError: '',

    async init() {
      const token = localStorage.getItem('fn_token');
      if (!token) return;
      const res = await fetch('/api/my-feeds', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
      if (res.ok) {
        const data = await res.json();
        this.feeds = data.data.map(f => ({ ...f, refreshing: false }));
      }
    },

    token() { return localStorage.getItem('fn_token'); },

    async addFeed() {
      if (!this.newName.trim() || !this.newUrl.trim()) return;
      this.addLoading = true; this.addError = '';
      try {
        const res = await fetch('/api/my-feeds', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer ' + this.token() },
          body: JSON.stringify({ name: this.newName.trim(), feed_url: this.newUrl.trim() }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Errore nell\'aggiunta.');
        this.feeds.unshift({ ...data.data, refreshing: false });
        this.newName = ''; this.newUrl = ''; this.showAdd = false;
      } catch (e) {
        this.addError = e.message;
      } finally {
        this.addLoading = false;
      }
    },

    async deleteFeed(id) {
      if (!confirm('Rimuovere questo feed? Verranno eliminati anche tutti gli articoli.')) return;
      const res = await fetch('/api/my-feeds/' + id, {
        method: 'DELETE',
        headers: { 'Authorization': 'Bearer ' + this.token(), 'Accept': 'application/json' },
      });
      if (res.ok) this.feeds = this.feeds.filter(f => f.id !== id);
    },

    async refreshFeed(feed) {
      feed.refreshing = true;
      try {
        const res = await fetch('/api/my-feeds/' + feed.id + '/refresh', {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + this.token(), 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (res.ok) {
          feed.articles_count = data.data.articles_count;
          feed.last_fetched_at = data.data.last_fetched_at;
        }
      } finally {
        feed.refreshing = false;
      }
    },

    timeAgo(dateStr) {
      if (!dateStr) return '';
      const diff = Math.floor((Date.now() - new Date(dateStr)) / 60000);
      if (diff < 1) return 'adesso';
      if (diff < 60) return diff + ' min fa';
      if (diff < 1440) return Math.floor(diff / 60) + 'h fa';
      return Math.floor(diff / 1440) + 'g fa';
    },
  };
}

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
