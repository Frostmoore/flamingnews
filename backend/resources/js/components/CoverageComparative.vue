<template>
  <div class="min-h-screen bg-[#F8F6F1]">

    <!-- Header -->
    <header class="border-b border-gray-300 bg-white sticky top-0 z-30">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="font-display text-2xl font-bold text-[#C41E3A] tracking-tight">FlamingNews</a>
        <nav class="flex items-center gap-1 text-sm">
          <a href="/" class="px-3 py-1.5 text-gray-600 hover:text-[#1A1A1A]">Feed</a>
          <a href="/coverage" class="px-3 py-1.5 font-semibold text-[#C41E3A] border-b-2 border-[#C41E3A]">Coverage</a>
        </nav>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">

      <!-- Titolo sezione -->
      <div class="mb-8">
        <h1 class="font-display text-3xl font-bold text-[#1A1A1A]">Coverage Comparativa</h1>
        <p class="text-gray-500 mt-1 text-sm">
          Lo stesso evento, raccontato da testate con orientamenti diversi.
        </p>
      </div>

      <!-- Se topic selezionato: dettaglio -->
      <template v-if="activeTopic">
        <button
          @click="activeTopic = null"
          class="mb-6 text-sm text-[#C41E3A] hover:underline flex items-center gap-1"
        >
          ← Tutti i topic
        </button>

        <TopicDetail
          :topic="activeTopic"
          :is-premium="isPremium"
          :is-analyzing="isAnalyzing"
          @generate-analysis="handleGenerateAnalysis"
        />
      </template>

      <!-- Lista topic -->
      <template v-else>

        <!-- Loading -->
        <div v-if="loading" class="space-y-4">
          <div v-for="n in 6" :key="n" class="bg-white p-5 animate-pulse">
            <div class="h-5 bg-gray-200 rounded w-2/3 mb-2"></div>
            <div class="h-3 bg-gray-100 rounded w-1/4"></div>
          </div>
        </div>

        <div v-else-if="error" class="text-center py-16 text-red-600">
          <p>{{ error }}</p>
          <button @click="load" class="mt-3 px-4 py-2 bg-[#C41E3A] text-white rounded text-sm">Riprova</button>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="t in topics"
            :key="t.id"
            class="bg-white border border-gray-200 hover:border-[#C41E3A] cursor-pointer transition-colors p-5 group"
            @click="openTopic(t.id)"
          >
            <div class="flex items-start justify-between gap-4">
              <div>
                <h2 class="font-display text-lg font-semibold text-[#1A1A1A] group-hover:text-[#C41E3A] transition-colors leading-snug">
                  {{ t.title }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">{{ t.article_count }} articoli</p>
              </div>
              <svg class="w-5 h-5 text-gray-300 group-hover:text-[#C41E3A] flex-shrink-0 mt-1 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>

            <!-- Keywords -->
            <div v-if="t.keywords?.length" class="flex flex-wrap gap-1 mt-3">
              <span
                v-for="kw in t.keywords.slice(0, 4)"
                :key="kw"
                class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"
              >{{ kw }}</span>
            </div>
          </div>
        </div>

        <!-- Paginazione -->
        <div v-if="meta.last_page > 1" class="mt-8 flex justify-center gap-2">
          <button v-if="meta.current_page > 1" @click="changePage(meta.current_page - 1)"
            class="px-4 py-2 border border-gray-300 text-sm hover:border-[#C41E3A] hover:text-[#C41E3A] transition-colors">
            ← Precedente
          </button>
          <span class="px-4 py-2 text-sm text-gray-500">{{ meta.current_page }} / {{ meta.last_page }}</span>
          <button v-if="meta.current_page < meta.last_page" @click="changePage(meta.current_page + 1)"
            class="px-4 py-2 border border-gray-300 text-sm hover:border-[#C41E3A] hover:text-[#C41E3A] transition-colors">
            Successiva →
          </button>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useTopics } from '../composables/useTopics';
import { useAuth } from '../composables/useAuth';
import TopicDetail from './TopicDetail.vue';

const { topics, topic, meta, loading, isAnalyzing, error, fetchTopics, fetchTopic, generateAnalysis } = useTopics();
const { isPremium } = useAuth();

const activeTopic = ref(null);

async function load(page = 1) {
  await fetchTopics({ page });
}

function changePage(page) {
  load(page);
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function openTopic(id) {
  const data = await fetchTopic(id);
  activeTopic.value = data;
}

async function handleGenerateAnalysis() {
  if (!activeTopic.value) return;
  const analysis = await generateAnalysis(activeTopic.value.id);
  if (analysis && activeTopic.value) {
    activeTopic.value.ai_analysis = analysis;
  }
}

onMounted(() => load());
</script>
