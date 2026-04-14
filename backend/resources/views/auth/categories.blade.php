@extends('layouts.app')
@section('title', 'I tuoi interessi')

@section('content')
<div class="min-h-screen bg-[#F8F6F1] flex items-center justify-center p-4">
  <div class="w-full max-w-md" x-data="categoriesForm()" x-init="init()">

    <a href="/" class="block font-display text-3xl font-bold text-[#C41E3A] text-center mb-8 tracking-tight">
      FlamingNews
    </a>

    <div class="bg-white border border-gray-200 p-8 shadow-sm">

      <h1 class="text-2xl font-bold text-[#1A1A1A] mb-2">Cosa vuoi leggere?</h1>
      <p class="text-sm text-gray-500 mb-8">
        Scegli i temi che ti interessano. Puoi cambiarli in qualsiasi momento dal profilo.
      </p>

      <div x-show="error" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded" x-text="error"></div>

      <div class="grid grid-cols-2 gap-3 mb-8">
        <template x-for="cat in categories" :key="cat.value">
          <button
            type="button"
            @click="toggleCategory(cat.value)"
            class="flex flex-col items-center gap-2 p-4 border-2 text-center transition-all"
            :class="selectedCategories.includes(cat.value)
              ? 'border-[#C41E3A] bg-red-50'
              : 'border-gray-200 bg-white hover:border-gray-300'"
          >
            <span x-text="cat.icon" class="text-2xl"></span>
            <span class="text-sm font-bold text-[#1A1A1A]" x-text="cat.label"></span>
            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center mt-1"
              :class="selectedCategories.includes(cat.value)
                ? 'border-[#C41E3A] bg-[#C41E3A]'
                : 'border-gray-300'">
              <span x-show="selectedCategories.includes(cat.value)" class="text-white text-xs font-bold">✓</span>
            </div>
          </button>
        </template>
      </div>

      <p class="text-xs text-gray-400 text-center mb-4">
        <span x-text="selectedCategories.length"></span> / 6 selezionate
      </p>

      <button
        @click="save"
        :disabled="loading || selectedCategories.length === 0"
        class="w-full bg-[#C41E3A] text-white py-3 text-sm font-bold tracking-wide hover:bg-red-800 transition-colors disabled:opacity-40"
      >
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
function categoriesForm() {
  return {
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
      { value: 'ambiente',   label: 'Ambiente',    icon: '🌿' },
      { value: 'istruzione', label: 'Istruzione',  icon: '📚' },
      { value: 'cibo',       label: 'Cibo',        icon: '🍕' },
      { value: 'viaggi',     label: 'Viaggi',      icon: '✈️' },
    ],
    init() {
      const token = localStorage.getItem('fn_token');
      if (!token) { window.location.href = '/login'; return; }
      // Pre-seleziona categorie esistenti
      const user = JSON.parse(localStorage.getItem('fn_user') || '{}');
      this.selectedCategories = user.preferred_categories ?? [];
    },
    toggleCategory(val) {
      const idx = this.selectedCategories.indexOf(val);
      idx === -1 ? this.selectedCategories.push(val) : this.selectedCategories.splice(idx, 1);
    },
    async save() {
      if (this.selectedCategories.length === 0) { this.error = 'Seleziona almeno un interesse.'; return; }
      this.loading = true; this.error = '';
      const token = localStorage.getItem('fn_token');
      try {
        const res = await fetch('/api/auth/categories', {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token,
          },
          body: JSON.stringify({ preferred_categories: this.selectedCategories }),
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
    skip() {
      window.location.href = '/';
    },
  };
}
</script>
@endsection
