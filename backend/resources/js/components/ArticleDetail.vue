<template>
  <div class="max-w-3xl mx-auto py-8 px-4">
    <div v-if="loading" class="animate-pulse space-y-4">
      <div class="h-8 bg-gray-200 rounded w-3/4"></div>
      <div class="h-4 bg-gray-100 rounded w-1/3"></div>
      <div class="aspect-[16/9] bg-gray-200 rounded"></div>
      <div class="space-y-2">
        <div v-for="n in 6" :key="n" class="h-4 bg-gray-100 rounded"></div>
      </div>
    </div>

    <article v-else-if="article">
      <!-- Breadcrumb -->
      <div class="text-xs text-gray-400 mb-4">
        <a href="/" class="hover:text-[#C41E3A]">Feed</a>
        <span class="mx-1">›</span>
        <span class="capitalize">{{ article.category }}</span>
      </div>

      <!-- Fonte e orientamento -->
      <div class="flex items-center gap-2 mb-3">
        <span
          v-if="article.political_lean"
          class="text-xs font-semibold px-2 py-0.5 rounded-full uppercase"
          :class="leanBadgeClass"
        >{{ leanLabel }}</span>
        <span class="text-sm font-semibold text-gray-700">{{ article.source_name }}</span>
        <span class="text-xs text-gray-400 ml-auto">{{ formattedDate }}</span>
      </div>

      <!-- Titolo -->
      <h1 class="font-display text-2xl md:text-3xl font-bold text-[#1A1A1A] leading-tight mb-4">
        {{ article.title }}
      </h1>

      <!-- Immagine -->
      <img
        v-if="article.url_to_image"
        :src="article.url_to_image"
        :alt="article.title"
        class="w-full aspect-[16/9] object-cover rounded mb-6"
      />

      <!-- Descrizione -->
      <p v-if="article.description" class="text-lg text-gray-600 leading-relaxed mb-6 font-medium border-l-4 border-[#C41E3A] pl-4">
        {{ article.description }}
      </p>

      <!-- Contenuto -->
      <div class="prose prose-gray max-w-none text-[#1A1A1A] leading-relaxed">
        <p>{{ article.content }}</p>
      </div>

      <!-- Link originale -->
      <div class="mt-8 pt-4 border-t border-gray-200">
        <a
          :href="article.url"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-2 text-sm text-[#C41E3A] font-semibold hover:underline"
        >
          Leggi l'articolo completo su {{ article.source_name }}
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
          </svg>
        </a>
      </div>

      <!-- Link al topic cluster -->
      <div v-if="article.topic_id" class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded text-sm">
        Questo articolo fa parte di un cluster tematico.
        <a :href="`/coverage?topic=${article.topic_id}`" class="text-[#C41E3A] font-semibold ml-1 hover:underline">
          Vedi la coverage comparativa →
        </a>
      </div>
    </article>

    <div v-else class="text-center py-16 text-gray-500">Articolo non trovato.</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useArticles } from '../composables/useArticles';

const props = defineProps({
  articleId: { type: [Number, String], default: null },
});

const { loading, error, fetchArticle } = useArticles();
const article = ref(null);

const leanMap = {
  left:          { label: 'Sinistra',      class: 'badge-left' },
  right:         { label: 'Destra',         class: 'badge-right' },
  center:        { label: 'Centro',         class: 'badge-center' },
  international: { label: 'Internazionale', class: 'badge-international' },
};

const leanBadgeClass = computed(() => leanMap[article.value?.political_lean]?.class ?? 'badge-center');
const leanLabel      = computed(() => leanMap[article.value?.political_lean]?.label ?? '');

const formattedDate = computed(() => {
  if (!article.value?.published_at) return '';
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'long', year: 'numeric' })
    .format(new Date(article.value.published_at));
});

onMounted(async () => {
  const id = props.articleId ?? new URLSearchParams(window.location.search).get('id')
    ?? window.location.pathname.split('/').pop();
  article.value = await fetchArticle(id);
});
</script>
