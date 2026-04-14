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

        <!-- Bottone Google -->
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
          <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Nome</label>
            <input type="text" x-model="name" required autocomplete="name"
              class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A]"
              placeholder="Mario Rossi" />
          </div>
          <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Email</label>
            <input type="email" x-model="email" required autocomplete="email"
              class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A]"
              placeholder="tu@esempio.it" />
          </div>
          <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Password</label>
            <input type="password" x-model="password" required autocomplete="new-password"
              class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A]"
              placeholder="Minimo 8 caratteri" />
          </div>
          <div class="mb-6">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Conferma Password</label>
            <input type="password" x-model="passwordConfirmation" required autocomplete="new-password"
              class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A]" />
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

      <!-- STEP 2: Selezione categorie preferite -->
      <div x-show="step === 2" x-cloak>
        <h1 class="text-xl font-bold text-[#1A1A1A] mb-2">Scegli i tuoi interessi</h1>
        <p class="text-sm text-gray-500 mb-6">Seleziona almeno un tema per personalizzare il tuo feed.</p>

        <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded" x-text="error"></div>

        <div class="grid grid-cols-2 gap-2 mb-6">
          <template x-for="cat in categories" :key="cat.value">
            <button
              type="button"
              @click="toggleCategory(cat.value)"
              class="flex items-center gap-2 p-3 border-2 text-left transition-all"
              :class="selectedCategories.includes(cat.value)
                ? 'border-[#C41E3A] bg-red-50 text-[#C41E3A]'
                : 'border-gray-200 bg-white text-gray-600 hover:border-gray-400'"
            >
              <span x-text="cat.icon" class="text-lg"></span>
              <span class="text-sm font-semibold" x-text="cat.label"></span>
              <span x-show="selectedCategories.includes(cat.value)" class="ml-auto text-[#C41E3A]">✓</span>
            </button>
          </template>
        </div>

        <button
          @click="register"
          :disabled="loading || selectedCategories.length === 0"
          class="w-full bg-[#C41E3A] text-white py-2.5 text-sm font-bold tracking-wide hover:bg-red-800 transition-colors disabled:opacity-40"
        >
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
    name: '', email: '', password: '', passwordConfirmation: '',
    selectedCategories: [],
    loading: false, error: '',
    categories: [
      { value: 'politica',   label: 'Politica',    icon: '🏛️' },
      { value: 'economia',   label: 'Economia',    icon: '📈' },
      { value: 'esteri',     label: 'Esteri',      icon: '🌍' },
      { value: 'tecnologia', label: 'Tecnologia',  icon: '💻' },
      { value: 'sport',      label: 'Sport',       icon: '⚽' },
      { value: 'cultura',    label: 'Cultura',     icon: '🎭' },
      { value: 'generale',   label: 'Generale',    icon: '🗞️' },
      { value: 'scienza',    label: 'Scienza',     icon: '🔬' },
      { value: 'salute',     label: 'Salute',      icon: '🏥' },
    ],
    nextStep() {
      this.error = '';
      if (this.password !== this.passwordConfirmation) {
        this.error = 'Le password non coincidono.';
        return;
      }
      if (this.password.length < 8) {
        this.error = 'La password deve essere di almeno 8 caratteri.';
        return;
      }
      this.step = 2;
    },
    toggleCategory(val) {
      const idx = this.selectedCategories.indexOf(val);
      idx === -1 ? this.selectedCategories.push(val) : this.selectedCategories.splice(idx, 1);
    },
    async register() {
      if (this.selectedCategories.length === 0) { this.error = 'Seleziona almeno un interesse.'; return; }
      this.loading = true; this.error = '';
      try {
        const res = await fetch('/api/auth/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({
            name: this.name, email: this.email,
            password: this.password, password_confirmation: this.passwordConfirmation,
            preferred_categories: this.selectedCategories,
          }),
        });
        const data = await res.json();
        if (!res.ok) {
          const msgs = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
          throw new Error(msgs || 'Errore nella registrazione.');
        }
        localStorage.setItem('fn_token', data.token);
        localStorage.setItem('fn_user', JSON.stringify(data.user));
        window.location.href = '/';
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
