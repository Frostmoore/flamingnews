@extends('layouts.app')
@section('title', 'Le tue testate')

@section('content')
<div class="min-h-screen bg-[#F8F6F1] flex items-center justify-center p-4">
  <div class="w-full max-w-sm" x-data="sourcesForm()" x-init="init()">

    <a href="/" class="block font-display text-3xl font-bold text-[#C41E3A] text-center mb-8 tracking-tight">
      FlamingNews
    </a>

    <div class="bg-white border border-gray-200 p-8 shadow-sm">

      <h1 class="text-xl font-bold text-[#1A1A1A] mb-1">Scegli le tue testate</h1>
      <p class="text-sm text-gray-500 mb-1">Seleziona i giornali che vuoi seguire.</p>
      <div class="flex items-center gap-1.5 mb-5">
        <svg class="w-3.5 h-3.5 text-[#C41E3A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 12h.01M12 12h.01M18 12h.01M6 6h.01M12 6h.01M18 6h.01M6 18h.01M12 18h.01M18 18h.01"/>
        </svg>
        <span class="text-[11px] font-semibold text-[#C41E3A] uppercase tracking-wide">Aggiornate via Feed RSS</span>
      </div>

      <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded" x-text="error"></div>

      <!-- Loading -->
      <div x-show="loadingSources" class="flex justify-center py-8">
        <svg class="animate-spin w-6 h-6 text-[#C41E3A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
      </div>

      <div x-show="!loadingSources" class="space-y-1.5 mb-4 max-h-72 overflow-y-auto pr-1">
        <template x-for="s in sources" :key="s.domain">
          <button type="button" @click="toggleSource(s.domain)"
            class="w-full flex items-center gap-2.5 px-3 py-2.5 border-2 text-left transition-all rounded"
            :class="selectedSources.includes(s.domain)
              ? 'border-[#C41E3A] bg-red-50'
              : 'border-gray-200 bg-white hover:border-gray-300'">
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

      <button @click="save" :disabled="loading || selectedSources.length === 0"
        class="w-full bg-[#C41E3A] text-white py-2.5 text-sm font-bold tracking-wide hover:bg-red-800 transition-colors disabled:opacity-40">
        <span x-show="!loading">Personalizza il mio feed →</span>
        <span x-show="loading">Salvataggio...</span>
      </button>

      <button @click="skip" class="w-full text-sm text-gray-400 hover:text-gray-600 mt-3 py-1">
        Salta per ora
      </button>
    </div>
  </div>
</div>

<script>
function sourcesForm() {
  return {
    selectedSources: [],
    sources: [],
    loadingSources: true,
    loading: false, error: '',

    leanColor(lean) {
      const map = { left:'#1D4ED8', 'center-left':'#60A5FA', center:'#6B7280', 'center-right':'#FB923C', right:'#DC2626', international:'#D97706', altro:'#7C3AED' };
      return map[lean] ?? '#9CA3AF';
    },

    async init() {
      const token = localStorage.getItem('fn_token');
      if (!token) { window.location.href = '/login'; return; }
      const user = JSON.parse(localStorage.getItem('fn_user') || '{}');
      this.selectedSources = user.preferred_sources ?? [];
      try {
        const res = await fetch('/api/sources');
        this.sources = await res.json();
      } finally {
        this.loadingSources = false;
      }
    },

    toggleSource(domain) {
      const idx = this.selectedSources.indexOf(domain);
      idx === -1 ? this.selectedSources.push(domain) : this.selectedSources.splice(idx, 1);
    },

    async save() {
      if (!this.selectedSources.length) { this.error = 'Seleziona almeno una testata.'; return; }
      this.loading = true; this.error = '';
      const token = localStorage.getItem('fn_token');
      try {
        const res = await fetch('/api/auth/sources', {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer ' + token },
          body: JSON.stringify({ preferred_sources: this.selectedSources }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Errore nel salvataggio.');
        localStorage.setItem('fn_user', JSON.stringify(data.user));
        window.location.href = '/';
      } catch (e) {
        this.error = e.message;
      } finally {
        this.loading = false;
      }
    },

    skip() { window.location.href = '/'; },
  };
}
</script>
@endsection
