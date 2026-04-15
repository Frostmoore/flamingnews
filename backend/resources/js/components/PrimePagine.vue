<template>
  <div class="min-h-screen bg-[#F8F6F1]">

    <!-- Header uguale al NewsFeed -->
    <header class="sticky top-0 z-30 bg-white shadow-sm">
      <div class="bg-[#C41E3A] h-1 w-full"></div>
      <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between border-b border-gray-100">
        <a href="/" class="font-display text-2xl font-bold text-[#1A1A1A] tracking-tight">
          Flaming<span class="text-[#C41E3A]">News</span>
        </a>
        <div class="flex items-center gap-3">
          <a href="/" class="text-sm text-gray-500 hover:text-[#C41E3A] transition-colors">← Feed</a>
        </div>
      </div>

      <!-- Titolo sezione -->
      <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-baseline gap-3">
          <h1 class="font-display text-xl font-bold text-[#1A1A1A]">Prime Pagine</h1>
          <span v-if="editionDate" class="text-xs text-gray-400 uppercase tracking-wide">
            {{ formatDate(editionDate) }}
          </span>
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">

      <!-- Skeleton -->
      <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div v-for="n in 10" :key="n" class="animate-pulse">
          <div class="aspect-[3/4] bg-gray-200 rounded"></div>
          <div class="mt-2 h-3 bg-gray-200 rounded w-3/4"></div>
        </div>
      </div>

      <!-- Errore -->
      <div v-else-if="error" class="text-center py-16 text-red-600">
        <p class="text-lg font-semibold">Errore nel caricamento</p>
        <p class="text-sm mt-1 text-gray-500">{{ error }}</p>
        <button @click="load" class="mt-4 px-4 py-2 bg-[#C41E3A] text-white text-sm">Riprova</button>
      </div>

      <!-- Griglia prime pagine -->
      <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
        <a
          v-for="pp in pagine"
          :key="pp.id"
          :href="pp.article_url"
          target="_blank"
          rel="noopener"
          class="group flex flex-col"
        >
          <!-- Immagine copertina -->
          <div class="relative overflow-hidden bg-gray-100 aspect-[3/4] border border-gray-200 group-hover:border-[#C41E3A] transition-colors duration-200">
            <!-- Badge orientamento -->
            <span
              v-if="pp.political_lean"
              class="absolute top-2 left-2 z-10 text-[9px] font-bold px-1.5 py-0.5 rounded text-white uppercase tracking-wide"
              :class="leanBgClass(pp.political_lean)"
            >{{ leanLabel(pp.political_lean) }}</span>

            <img
              :src="pp.image_url"
              :alt="pp.source_name"
              class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-300"
              loading="lazy"
              @error="onImgError"
            />

            <!-- Overlay con nome testata -->
            <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/70 to-transparent pt-8 pb-2 px-2">
              <p class="text-white text-xs font-bold uppercase tracking-wide leading-tight">
                {{ pp.source_name }}
              </p>
            </div>
          </div>

          <!-- Headline -->
          <p class="mt-2 text-xs text-gray-600 leading-snug line-clamp-2 group-hover:text-[#C41E3A] transition-colors">
            {{ pp.headline || pp.source_name }}
          </p>
        </a>
      </div>

      <!-- Vuoto -->
      <div v-if="!loading && !error && pagine.length === 0" class="text-center py-16 text-gray-400">
        <p class="text-lg">Nessuna prima pagina disponibile.</p>
        <p class="text-sm mt-1">Verrà aggiornata domani mattina.</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const pagine      = ref([]);
const loading     = ref(false);
const error       = ref(null);
const editionDate = ref(null);

async function load() {
  loading.value = true;
  error.value   = null;
  try {
    const res = await axios.get('/api/prima-pagine');
    pagine.value      = res.data.data;
    editionDate.value = pagine.value[0]?.edition_date ?? null;
  } catch (e) {
    error.value = e.response?.data?.message || 'Errore nel caricamento.';
  } finally {
    loading.value = false;
  }
}

function formatDate(dateStr) {
  return new Intl.DateTimeFormat('it-IT', { weekday: 'long', day: 'numeric', month: 'long' })
    .format(new Date(dateStr));
}

function onImgError(e) {
  e.target.closest('a')?.remove(); // rimuove la card se l'immagine non carica
}

const leanBgMap = {
  left: 'bg-blue-600', center: 'bg-gray-500', right: 'bg-red-600',
  international: 'bg-amber-500', altro: 'bg-purple-500',
};
const leanLabelMap = {
  left: 'Sinistra', center: 'Centro', right: 'Destra',
  international: "Int'l", altro: 'Neutro',
};
function leanBgClass(lean) { return leanBgMap[lean] ?? 'bg-gray-400'; }
function leanLabel(lean)   { return leanLabelMap[lean] ?? lean; }

onMounted(load);
</script>
