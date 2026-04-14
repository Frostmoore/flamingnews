<template>
  <div>
    <!-- Titolo topic -->
    <h1 class="font-display text-2xl md:text-3xl font-bold text-[#1A1A1A] leading-tight mb-1">
      {{ topic.title }}
    </h1>
    <p class="text-sm text-gray-400 mb-6">{{ topic.article_count }} fonti analizzate</p>

    <!-- Legenda orientamenti -->
    <div class="flex flex-wrap gap-3 mb-6 text-xs font-semibold">
      <span class="px-3 py-1 badge-left rounded-full">Sinistra</span>
      <span class="px-3 py-1 badge-center rounded-full">Centro</span>
      <span class="px-3 py-1 badge-right rounded-full">Destra</span>
      <span class="px-3 py-1 badge-international rounded-full">Internazionale</span>
    </div>

    <!-- Free tier avviso -->
    <div v-if="!isPremium" class="mb-5 p-3 bg-gray-50 border border-gray-200 rounded text-sm text-gray-600">
      <span class="font-semibold">Piano Free:</span> visualizzi massimo 3 fonti per cluster.
      <a href="/premium" class="text-[#C41E3A] font-semibold ml-1 hover:underline">Passa a Premium →</a>
    </div>

    <!-- Griglia orientamenti -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
      <div
        v-for="lean in leanOrder"
        :key="lean"
        v-show="(topic.sources?.[lean]?.length ?? 0) > 0"
        class="border-l-4 rounded-lg p-4 space-y-3"
        :class="leanCardClass(lean)"
      >
        <h3 class="text-sm font-bold uppercase tracking-widest" :class="leanTextClass(lean)">
          {{ leanLabels[lean] }}
        </h3>

        <div
          v-for="article in (topic.sources?.[lean] ?? [])"
          :key="article.id"
          class="border-b border-gray-100 pb-3 last:border-0 last:pb-0"
        >
          <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-1"
            :class="leanBadgeClass(lean)">
            {{ article.source_name }}
          </span>
          <a
            :href="article.url"
            target="_blank"
            rel="noopener noreferrer"
            class="block text-sm font-semibold text-[#1A1A1A] hover:text-[#C41E3A] leading-snug transition-colors"
          >
            {{ article.title }}
          </a>
          <p v-if="article.description" class="text-xs text-gray-500 mt-1 line-clamp-2">
            {{ article.description }}
          </p>
        </div>
      </div>
    </div>

    <!-- Pannello AI (solo Premium) -->
    <div v-if="isPremium">
      <AiAnalysisPanel
        :analysis="topic.ai_analysis"
        :generated-at="topic.ai_generated_at"
        :is-analyzing="isAnalyzing"
        @generate="$emit('generate-analysis')"
      />
    </div>
    <div v-else class="mt-6 p-5 border border-dashed border-gray-300 rounded-lg text-center">
      <p class="text-sm text-gray-500">
        L'analisi comparativa AI è disponibile per gli utenti
        <a href="/premium" class="text-[#C41E3A] font-semibold hover:underline">Premium</a>.
      </p>
    </div>
  </div>
</template>

<script setup>
import AiAnalysisPanel from './AiAnalysisPanel.vue';

const props = defineProps({
  topic:       { type: Object,  required: true },
  isPremium:   { type: Boolean, default: false },
  isAnalyzing: { type: Boolean, default: false },
});

defineEmits(['generate-analysis']);

const leanOrder  = ['left', 'center', 'right', 'international'];
const leanLabels = {
  left:          'Sinistra',
  center:        'Centro',
  right:         'Destra',
  international: 'Internazionale',
};

const cardClasses  = { left: 'lean-left', center: 'lean-center', right: 'lean-right', international: 'lean-international' };
const textClasses  = { left: 'text-blue-700', center: 'text-gray-600', right: 'text-red-700', international: 'text-amber-700' };
const badgeClasses = { left: 'badge-left', center: 'badge-center', right: 'badge-right', international: 'badge-international' };

const leanCardClass  = (l) => cardClasses[l]  ?? 'lean-center';
const leanTextClass  = (l) => textClasses[l]  ?? 'text-gray-600';
const leanBadgeClass = (l) => badgeClasses[l] ?? 'badge-center';
</script>
