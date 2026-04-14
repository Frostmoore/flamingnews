<template>
  <div class="min-h-screen bg-[#F8F6F1]">

    <!-- Header: striscia rossa + logo + auth -->
    <header class="sticky top-0 z-30 bg-white shadow-sm">
      <!-- Striscia rossa superiore -->
      <div class="bg-[#C41E3A] h-1 w-full"></div>

      <!-- Logo + auth -->
      <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between border-b border-gray-100">
        <a href="/" class="font-display text-2xl font-bold text-[#1A1A1A] tracking-tight">
          Flaming<span class="text-[#C41E3A]">News</span>
        </a>
        <div class="flex items-center gap-3">
          <span v-if="isAuthenticated" class="text-xs text-gray-400 hidden sm:inline">{{ userName }}</span>
          <button
            @click="toggleAuth"
            class="px-4 py-1.5 text-xs font-bold tracking-wide border border-gray-300 hover:border-[#C41E3A] hover:text-[#C41E3A] transition-colors uppercase"
          >{{ isAuthenticated ? 'Esci' : 'Accedi' }}</button>
        </div>
      </div>

      <!-- Barra categorie — scrollabile orizzontalmente -->
      <div class="border-b border-gray-200 bg-white">
        <div class="max-w-7xl mx-auto px-4">
          <div class="flex overflow-x-auto scrollbar-hide gap-0 -mb-px">
            <button
              v-for="cat in categories"
              :key="String(cat.value)"
              @click="selectCategory(cat.value)"
              class="flex-shrink-0 px-4 py-3 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors duration-150"
              :class="activeCategory === cat.value
                ? 'border-[#C41E3A] text-[#C41E3A]'
                : 'border-transparent text-gray-500 hover:text-[#1A1A1A] hover:border-gray-300'"
            >{{ cat.label }}</button>
          </div>
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-6">

      <!-- Loading skeleton -->
      <div v-if="loading" class="articles-grid">
        <div
          v-for="n in 9"
          :key="n"
          class="bg-white animate-pulse"
        >
          <div class="aspect-[16/9] bg-gray-200"></div>
          <div class="p-4 space-y-2">
            <div class="h-3 bg-gray-200 rounded w-1/3"></div>
            <div class="h-4 bg-gray-200 rounded"></div>
            <div class="h-4 bg-gray-200 rounded w-4/5"></div>
            <div class="h-3 bg-gray-100 rounded w-1/2 mt-2"></div>
          </div>
        </div>
      </div>

      <!-- Errore -->
      <div v-else-if="error" class="text-center py-16 text-red-600">
        <p class="text-lg font-semibold">Errore nel caricamento</p>
        <p class="text-sm mt-1">{{ error }}</p>
        <button @click="load" class="mt-4 px-4 py-2 bg-[#C41E3A] text-white rounded text-sm">Riprova</button>
      </div>

      <!-- Griglia articoli — layout editoriale asimmetrico -->
      <template v-else>
        <!-- Primo articolo in evidenza -->
        <div v-if="articles.length > 0" class="mb-6">
          <ArticleCard
            :article="articles[0]"
            class="md:flex md:gap-6"
            @click="openArticle"
            @like="toggleLike"
          />
        </div>

        <!-- Griglia 3 colonne con ad ogni N articoli -->
        <div class="articles-grid">
          <template v-for="(item, index) in feedItems" :key="item.type + '-' + index">
            <!-- Annuncio full-width che rompe la griglia -->
            <div v-if="item.type === 'ad'" class="col-span-full">
              <AdUnit
                :publisher-id="adsensePublisher"
                :slot="adsenseFeedSlot"
              />
            </div>
            <!-- Articolo normale -->
            <ArticleCard
              v-else
              :article="item.article"
              :compact="true"
              @click="openArticle"
              @like="toggleLike"
            />
          </template>
        </div>

        <!-- Paginazione -->
        <div v-if="meta.last_page > 1" class="mt-8 flex justify-center gap-2">
          <button
            v-if="meta.current_page > 1"
            @click="changePage(meta.current_page - 1)"
            class="px-4 py-2 border border-gray-300 text-sm hover:border-[#C41E3A] hover:text-[#C41E3A] transition-colors"
          >← Precedente</button>
          <span class="px-4 py-2 text-sm text-gray-500">
            {{ meta.current_page }} / {{ meta.last_page }}
          </span>
          <button
            v-if="meta.current_page < meta.last_page"
            @click="changePage(meta.current_page + 1)"
            class="px-4 py-2 border border-gray-300 text-sm hover:border-[#C41E3A] hover:text-[#C41E3A] transition-colors"
          >Successiva →</button>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useArticles } from '../composables/useArticles';
import { useAuth } from '../composables/useAuth';
import ArticleCard from './ArticleCard.vue';
import AdUnit from './AdUnit.vue';

const props = defineProps({
  adsensePublisher:  { type: String, default: '' },
  adsenseFeedSlot:   { type: String, default: '' },
  adsenseFrequency:  { type: String, default: '6' },
});

const { articles, meta, loading, error, fetchArticles, toggleLike } = useArticles();
const { user, isAuthenticated, logout } = useAuth();
const userName = computed(() => user.value?.name ?? '');

const activeCategory = ref(null);

// Intercala un placeholder annuncio ogni N articoli (escluso il primo in evidenza)
const feedItems = computed(() => {
  const freq = parseInt(props.adsenseFrequency) || 6;
  const rest = articles.value.slice(1);
  const items = [];
  rest.forEach((article, i) => {
    if (i > 0 && i % freq === 0) {
      items.push({ type: 'ad' });
    }
    items.push({ type: 'article', article });
  });
  return items;
});

const categories = [
  { value: null,          label: 'Tutte' },
  { value: 'politica',    label: 'Politica' },
  { value: 'economia',    label: 'Economia' },
  { value: 'esteri',      label: 'Esteri' },
  { value: 'tecnologia',  label: 'Tech' },
  { value: 'sport',       label: 'Sport' },
  { value: 'cultura',     label: 'Cultura' },
  { value: 'generale',    label: 'Generale' },
  { value: 'scienza',     label: 'Scienza' },
  { value: 'salute',      label: 'Salute' },
  { value: 'ambiente',    label: 'Ambiente' },
  { value: 'istruzione',  label: 'Istruzione' },
  { value: 'cibo',        label: 'Cibo' },
  { value: 'viaggi',      label: 'Viaggi' },
];

async function load(page = 1) {
  await fetchArticles({ category: activeCategory.value, page });
}

function selectCategory(value) {
  activeCategory.value = value;
  load(1);
}

function changePage(page) {
  load(page);
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function openArticle(article) {
  window.open(article.url, '_blank', 'noopener,noreferrer');
}

function toggleAuth() {
  if (isAuthenticated.value) {
    logout().then(() => window.location.reload());
  } else {
    window.location.href = '/login';
  }
}

onMounted(() => load());
</script>
