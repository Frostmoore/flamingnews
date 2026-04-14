<template>
  <div class="mt-6 border border-amber-200 bg-amber-50 rounded-lg p-5">

    <!-- Header -->
    <div class="flex items-center gap-2 mb-3">
      <span class="text-xs font-bold uppercase tracking-widest text-amber-700 bg-amber-200 px-2 py-0.5 rounded">
        Analisi AI
      </span>
      <span v-if="generatedAt" class="text-xs text-amber-600">
        Generata il {{ formattedDate }}
      </span>
    </div>

    <!-- Testo analisi -->
    <div v-if="analysis" class="prose prose-sm max-w-none text-[#1A1A1A]">
      <p class="leading-relaxed whitespace-pre-line">{{ analysis }}</p>
    </div>

    <!-- Bottone genera (solo se non ancora presente) -->
    <div v-else-if="!isAnalyzing">
      <p class="text-sm text-amber-700 mb-3">
        Genera un'analisi comparativa AI su come le diverse testate inquadrano questo evento.
      </p>
      <button
        @click="$emit('generate')"
        class="px-5 py-2 bg-amber-600 text-white text-sm font-semibold rounded hover:bg-amber-700 transition-colors"
      >
        Genera analisi
      </button>
    </div>

    <!-- Loading state -->
    <div v-else class="flex items-center gap-3 text-amber-700">
      <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
      </svg>
      <span class="text-sm font-medium">Analisi in corso con Claude AI...</span>
    </div>

    <!-- Disclaimer -->
    <p class="text-xs text-amber-600 mt-4 border-t border-amber-200 pt-3">
      Analisi generata da Claude AI (Anthropic). Scopo informativo — verifica sempre le fonti originali.
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  analysis:    { type: String,  default: null },
  generatedAt: { type: String,  default: null },
  isAnalyzing: { type: Boolean, default: false },
});

defineEmits(['generate']);

const formattedDate = computed(() => {
  if (!props.generatedAt) return '';
  return new Intl.DateTimeFormat('it-IT', {
    day: '2-digit', month: 'short', year: 'numeric',
  }).format(new Date(props.generatedAt));
});
</script>
