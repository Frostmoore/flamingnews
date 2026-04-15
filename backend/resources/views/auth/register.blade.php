@extends('layouts.app')
@section('title', 'Registrati')

@section('content')
<div class="min-h-screen bg-[#F8F6F1] flex items-center justify-center p-4">
  <div class="w-full max-w-sm">

    <a href="/" class="block font-display text-3xl font-bold text-[#C41E3A] text-center mb-8 tracking-tight">
      FlamingNews
    </a>

    <div class="bg-white border border-gray-200 p-8 shadow-sm" x-data="registerForm()">

      <!-- STEP 1: Dati account -->
      <div x-show="step === 1">
        <h1 class="text-xl font-bold text-[#1A1A1A] mb-6">Crea il tuo account</h1>

        <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded" x-text="error"></div>

        <!-- Google -->
        <a href="/api/auth/google/redirect"
          class="flex items-center justify-center gap-3 w-full border border-gray-300 bg-white text-[#1A1A1A] py-2.5 text-sm font-semibold rounded hover:bg-gray-50 transition-colors mb-4">
          <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Registrati con Google
        </a>

        <div class="flex items-center gap-3 mb-4">
          <div class="flex-1 h-px bg-gray-200"></div>
          <span class="text-xs text-gray-400 uppercase tracking-wider">oppure</span>
          <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        <form @submit.prevent="nextStep">
          <!-- Nome -->
          <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Nome</label>
            <input type="text" x-model="name" required autocomplete="name"
              class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A]"
              placeholder="Mario Rossi" />
          </div>

          <!-- Username -->
          <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Username</label>
            <input type="text" x-model="username" required autocomplete="username"
              class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A]"
              placeholder="mario_rossi" pattern="[a-zA-Z0-9_\-]+" />
            <p class="text-[11px] text-gray-400 mt-1">Solo lettere, numeri, trattini e underscore.</p>
          </div>

          <!-- Email -->
          <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Email</label>
            <input type="email" x-model="email" required autocomplete="email"
              class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A]"
              placeholder="tu@esempio.it" />
          </div>

          <!-- Password con occhietto -->
          <div class="mb-3">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Password</label>
            <div class="relative">
              <input :type="showPass ? 'text' : 'password'" x-model="password" required autocomplete="new-password"
                class="w-full border border-gray-300 px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-[#C41E3A]"
                placeholder="Minimo 8 caratteri" />
              <button type="button" @click="showPass = !showPass"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                <!-- Eye -->
                <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <!-- Eye-off -->
                <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>

            <!-- Barra di forza password -->
            <div x-show="password.length > 0" class="mt-2">
              <div class="flex gap-1 mb-2">
                <template x-for="i in 5" :key="i">
                  <div class="flex-1 h-1 rounded-full transition-all duration-300"
                    :class="strength.score >= i ? strength.color : 'bg-gray-200'"></div>
                </template>
              </div>
              <div class="grid grid-cols-2 gap-x-4 gap-y-0.5">
                <template x-for="req in strength.reqs" :key="req.label">
                  <div class="flex items-center gap-1.5">
                    <svg x-show="req.ok" class="w-3 h-3 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="!req.ok" class="w-3 h-3 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="9"/>
                    </svg>
                    <span class="text-[11px]" :class="req.ok ? 'text-green-600' : 'text-gray-400'" x-text="req.label"></span>
                  </div>
                </template>
              </div>
            </div>
          </div>

          <!-- Conferma Password -->
          <div class="mb-6">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Conferma Password</label>
            <div class="relative">
              <input :type="showPassConf ? 'text' : 'password'" x-model="passwordConfirmation" required autocomplete="new-password"
                class="w-full border border-gray-300 px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-[#C41E3A]" />
              <button type="button" @click="showPassConf = !showPassConf"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                <svg x-show="!showPassConf" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg x-show="showPassConf" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit"
            class="w-full bg-[#C41E3A] text-white py-2.5 text-sm font-bold tracking-wide hover:bg-red-800 transition-colors">
            Continua →
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-5">
          Hai già un account?
          <a href="/login" class="text-[#C41E3A] font-bold hover:underline">Accedi</a>
        </p>
      </div>

      <!-- STEP 2: Selezione testate -->
      <div x-show="step === 2" x-cloak>
        <h1 class="text-xl font-bold text-[#1A1A1A] mb-1">Scegli le tue testate</h1>
        <p class="text-sm text-gray-500 mb-1">Seleziona i giornali che vuoi seguire.</p>
        <div class="flex items-center gap-1.5 mb-5">
          <svg class="w-3.5 h-3.5 text-[#C41E3A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12h.01M12 12h.01M18 12h.01M6 6h.01M12 6h.01M18 6h.01M6 18h.01M12 18h.01M18 18h.01"/>
          </svg>
          <span class="text-[11px] font-semibold text-[#C41E3A] uppercase tracking-wide">Aggiornate via Feed RSS</span>
        </div>

        <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded" x-text="error"></div>

        <!-- Loading testate -->
        <div x-show="loadingSources" class="flex justify-center py-8">
          <svg class="animate-spin w-6 h-6 text-[#C41E3A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
          </svg>
        </div>

        <div x-show="!loadingSources" class="space-y-1.5 mb-5 max-h-72 overflow-y-auto pr-1">
          <template x-for="s in sources" :key="s.domain">
            <button type="button" @click="toggleSource(s.domain)"
              class="w-full flex items-center gap-2.5 px-3 py-2.5 border-2 text-left transition-all rounded"
              :class="selectedSources.includes(s.domain)
                ? 'border-[#C41E3A] bg-red-50'
                : 'border-gray-200 bg-white hover:border-gray-300'">
              <!-- Indicatore orientamento politico -->
              <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="`background:${leanColor(s.political_lean)}`"></span>
              <span class="flex-1 min-w-0">
                <span class="block text-sm font-semibold text-[#1A1A1A]" x-text="s.name"></span>
                <span class="block text-[11px] text-gray-400" x-text="s.domain"></span>
              </span>
              <span class="text-[10px] font-bold text-[#C41E3A] bg-red-50 border border-[#C41E3A]/20 px-1.5 py-0.5 rounded flex-shrink-0">RSS</span>
              <svg x-show="selectedSources.includes(s.domain)" class="w-4 h-4 text-[#C41E3A] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </button>
          </template>
        </div>

        <p class="text-xs text-gray-400 mb-4">
          <span x-text="selectedSources.length"></span> testate selezionate
          <span class="text-gray-300 mx-1">·</span>
          <button type="button" @click="selectedSources = sources.map(s => s.domain)" class="text-[#C41E3A] hover:underline">Tutte</button>
          <span class="text-gray-300 mx-1">·</span>
          <button type="button" @click="selectedSources = []" class="text-gray-400 hover:underline">Nessuna</button>
        </p>

        <button @click="doRegister" :disabled="loading || selectedSources.length === 0"
          class="w-full bg-[#C41E3A] text-white py-2.5 text-sm font-bold tracking-wide hover:bg-red-800 transition-colors disabled:opacity-40">
          <span x-show="!loading">Inizia a leggere</span>
          <span x-show="loading">Creazione account...</span>
        </button>

        <button @click="step = 1" class="w-full text-sm text-gray-400 hover:text-gray-600 mt-3 py-1">← Indietro</button>
      </div>

    </div>
  </div>
</div>

<script>
function registerForm() {
  return {
    step: 1,
    name: '', username: '', email: '', password: '', passwordConfirmation: '',
    showPass: false, showPassConf: false,
    selectedSources: [],
    sources: [],
    loadingSources: false,
    loading: false, error: '',

    get strength() {
      const p = this.password;
      const reqs = [
        { label: '8+ caratteri',       ok: p.length >= 8 },
        { label: 'Maiuscola',          ok: /[A-Z]/.test(p) },
        { label: 'Minuscola',          ok: /[a-z]/.test(p) },
        { label: 'Numero',             ok: /[0-9]/.test(p) },
        { label: 'Carattere speciale', ok: /[^A-Za-z0-9]/.test(p) },
      ];
      const score = reqs.filter(r => r.ok).length;
      const colors = ['', 'bg-red-400', 'bg-orange-400', 'bg-yellow-400', 'bg-lime-400', 'bg-green-500'];
      return { reqs, score, color: colors[score] || 'bg-gray-200' };
    },

    leanColor(lean) {
      const map = { left:'#1D4ED8', 'center-left':'#60A5FA', center:'#6B7280', 'center-right':'#FB923C', right:'#DC2626', international:'#D97706', altro:'#7C3AED' };
      return map[lean] ?? '#9CA3AF';
    },

    async nextStep() {
      this.error = '';
      if (this.password !== this.passwordConfirmation) { this.error = 'Le password non coincidono.'; return; }
      if (this.strength.score < 5) {
        const missing = this.strength.reqs.filter(r => !r.ok).map(r => r.label).join(', ');
        this.error = 'Password non sicura. Mancano: ' + missing + '.';
        return;
      }
      this.step = 2;
      await this.loadSources();
    },

    async loadSources() {
      if (this.sources.length) return;
      this.loadingSources = true;
      try {
        const res = await fetch('/api/sources');
        this.sources = await res.json();
        // Pre-seleziona tutte le testate
        this.selectedSources = this.sources.map(s => s.domain);
      } finally {
        this.loadingSources = false;
      }
    },

    toggleSource(domain) {
      const idx = this.selectedSources.indexOf(domain);
      idx === -1 ? this.selectedSources.push(domain) : this.selectedSources.splice(idx, 1);
    },

    async doRegister() {
      if (!this.selectedSources.length) { this.error = 'Seleziona almeno una testata.'; return; }
      this.loading = true; this.error = '';
      try {
        const res = await fetch('/api/auth/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({
            name: this.name, username: this.username, email: this.email,
            password: this.password, password_confirmation: this.passwordConfirmation,
            preferred_sources: this.selectedSources,
          }),
        });
        const data = await res.json();
        if (!res.ok) {
          const msgs = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
          throw new Error(msgs || 'Errore nella registrazione.');
        }
        localStorage.setItem('fn_token', data.token);
        localStorage.setItem('fn_user', JSON.stringify(data.user));
        window.location.href = '/email-sent';
      } catch (e) {
        this.error = e.message;
        this.step = 1;
      } finally {
        this.loading = false;
      }
    },
  };
}
</script>
@endsection
