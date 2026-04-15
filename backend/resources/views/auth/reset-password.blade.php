@extends('layouts.app')
@section('title', 'Nuova password')

@section('content')
<div class="min-h-screen bg-[#F8F6F1] flex items-center justify-center p-4">
  <div class="w-full max-w-sm">

    <a href="/" class="block font-display text-3xl font-bold text-[#C41E3A] text-center mb-8 tracking-tight">
      FlamingNews
    </a>

    <div class="bg-white border border-gray-200 p-8 shadow-sm" x-data="resetForm()">

      <template x-if="!done">
        <div>
          <h1 class="text-xl font-bold text-[#1A1A1A] mb-2">Nuova password</h1>
          <p class="text-sm text-gray-500 mb-6">Scegli una password sicura per il tuo account.</p>

          <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded" x-text="error"></div>

          <form @submit.prevent="reset">
            <!-- Password -->
            <div class="mb-3">
              <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Nuova password</label>
              <div class="relative">
                <input :type="showPass ? 'text' : 'password'" x-model="password" required autocomplete="new-password"
                  class="w-full border border-gray-300 px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-[#C41E3A]"
                  placeholder="Minimo 8 caratteri" />
                <button type="button" @click="showPass = !showPass"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                  </svg>
                </button>
              </div>
              <!-- Barra forza -->
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
                      <svg x-show="req.ok" class="w-3 h-3 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                      <svg x-show="!req.ok" class="w-3 h-3 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg>
                      <span class="text-[11px]" :class="req.ok ? 'text-green-600' : 'text-gray-400'" x-text="req.label"></span>
                    </div>
                  </template>
                </div>
              </div>
            </div>

            <!-- Conferma -->
            <div class="mb-6">
              <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Conferma password</label>
              <div class="relative">
                <input :type="showConf ? 'text' : 'password'" x-model="passwordConfirmation" required autocomplete="new-password"
                  class="w-full border border-gray-300 px-3 py-2.5 pr-10 text-sm focus:outline-none focus:border-[#C41E3A]" />
                <button type="button" @click="showConf = !showConf"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <svg x-show="!showConf" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  <svg x-show="showConf" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                  </svg>
                </button>
              </div>
            </div>

            <button type="submit" :disabled="loading || strength.score < 5"
              class="w-full bg-[#C41E3A] text-white py-2.5 text-sm font-bold tracking-wide hover:bg-red-800 transition-colors disabled:opacity-50">
              <span x-show="!loading">Salva nuova password</span>
              <span x-show="loading">Salvataggio...</span>
            </button>
          </form>
        </div>
      </template>

      <template x-if="done">
        <div class="text-center">
          <div class="w-16 h-16 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-5 text-3xl">✅</div>
          <h2 class="text-lg font-bold text-[#1A1A1A] mb-3">Password aggiornata!</h2>
          <p class="text-sm text-gray-500 leading-relaxed mb-5">
            La tua password è stata reimpostata con successo.<br>
            Ora puoi accedere con la nuova password.
          </p>
          <a href="/login" class="block w-full bg-[#C41E3A] text-white py-2.5 text-sm font-bold text-center hover:bg-red-800 transition-colors">
            Vai al login →
          </a>
        </div>
      </template>

    </div>
  </div>
</div>

<script>
function resetForm() {
  const params = new URLSearchParams(location.search);
  return {
    token: params.get('token') ?? '',
    email: params.get('email') ?? '',
    password: '', passwordConfirmation: '',
    showPass: false, showConf: false,
    loading: false, error: '', done: false,

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

    async reset() {
      if (this.password !== this.passwordConfirmation) { this.error = 'Le password non coincidono.'; return; }
      this.loading = true; this.error = '';
      try {
        const res = await fetch('/api/auth/reset-password', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({
            token: this.token, email: this.email,
            password: this.password, password_confirmation: this.passwordConfirmation,
          }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Link non valido o scaduto.');
        this.done = true;
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
