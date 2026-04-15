@extends('layouts.app')
@section('title', 'Password dimenticata')

@section('content')
<div class="min-h-screen bg-[#F8F6F1] flex items-center justify-center p-4">
  <div class="w-full max-w-sm">

    <a href="/" class="block font-display text-3xl font-bold text-[#C41E3A] text-center mb-8 tracking-tight">
      FlamingNews
    </a>

    <div class="bg-white border border-gray-200 p-8 shadow-sm" x-data="forgotForm()">

      <template x-if="!sent">
        <div>
          <h1 class="text-xl font-bold text-[#1A1A1A] mb-2">Password dimenticata?</h1>
          <p class="text-sm text-gray-500 mb-6">Inserisci la tua email e ti invieremo un link per impostare una nuova password.</p>

          <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded" x-text="error"></div>

          <form @submit.prevent="send">
            <div class="mb-5">
              <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Email</label>
              <input type="email" x-model="email" required autocomplete="email"
                class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-[#C41E3A] transition-colors"
                placeholder="tu@esempio.it" />
            </div>
            <button type="submit" :disabled="loading"
              class="w-full bg-[#C41E3A] text-white py-2.5 text-sm font-bold tracking-wide hover:bg-red-800 transition-colors disabled:opacity-50">
              <span x-show="!loading">Invia link di ripristino</span>
              <span x-show="loading">Invio in corso...</span>
            </button>
          </form>
        </div>
      </template>

      <template x-if="sent">
        <div class="text-center">
          <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-5 text-3xl">📬</div>
          <h2 class="text-lg font-bold text-[#1A1A1A] mb-3">Controlla la tua email</h2>
          <p class="text-sm text-gray-500 leading-relaxed mb-5">
            Se l'indirizzo è associato a un account, riceverai un'email con il link per reimpostare la password.
          </p>
          <div class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded p-3">
            Non vedi l'email? Controlla la cartella spam.
          </div>
        </div>
      </template>

      <p class="text-center text-sm text-gray-400 mt-6">
        <a href="/login" class="text-[#C41E3A] font-semibold hover:underline">← Torna al login</a>
      </p>
    </div>

  </div>
</div>

<script>
function forgotForm() {
  return {
    email: '', loading: false, error: '', sent: false,
    async send() {
      this.loading = true; this.error = '';
      try {
        const res = await fetch('/api/auth/forgot-password', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ email: this.email }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Errore nell\'invio.');
        this.sent = true;
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
