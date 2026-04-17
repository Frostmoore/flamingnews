<template>
  <article
    class="group cursor-pointer bg-white border border-gray-200 hover:border-[#C41E3A] transition-colors duration-200 flex"
    :class="featured ? 'flex-col md:flex-row' : 'flex-col'"
    @click="$emit('click', article)"
  >
    <!-- Immagine -->
    <div
      v-if="article.url_to_image"
      class="overflow-hidden flex-shrink-0"
      :class="featured ? 'w-full aspect-[16/9] md:w-1/2 md:aspect-auto' : 'w-full aspect-[16/9]'"
    >
      <img
        :src="article.url_to_image"
        :alt="decodeHtml(article.title)"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        loading="lazy"
      />
    </div>

    <!-- Wrapper colonna destra (o inferiore): contiene content + spacer + barra -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Contenuto testuale -->
      <div class="p-4 flex flex-col">
        <!-- Badge fonte + orientamento + data -->
        <div class="flex items-center gap-2 mb-2">
          <span
            v-if="article.political_lean"
            class="text-xs font-semibold px-2 py-0.5 rounded-full uppercase tracking-wide"
            :class="leanBadgeClass"
          >{{ leanLabel }}</span>
          <span class="text-xs text-gray-500 truncate">{{ article.source_name }}</span>
          <span class="text-xs text-gray-400 ml-auto">{{ formattedDate }}</span>
        </div>

        <!-- Titolo -->
        <h3
          class="font-display text-[#1A1A1A] leading-snug mb-3 line-clamp-3"
          :class="featured ? 'text-lg md:text-2xl' : compact ? 'text-base' : 'text-lg'"
        >{{ decodeHtml(article.title) }}</h3>

        <!-- Descrizione -->
        <p v-if="!compact && article.description" class="text-sm text-gray-600 line-clamp-2 mb-3">
          {{ decodeHtml(article.description) }}
        </p>

        <!-- Footer: categoria + azioni articolo principale -->
        <div class="flex items-center justify-between gap-2 mb-3">
          <span class="text-xs text-[#C41E3A] font-semibold uppercase tracking-wider">
            {{ article.category }}
          </span>
          <div class="flex items-center gap-3" @click.stop>
            <!-- Like articolo principale -->
            <button
              @click="$emit('like', article)"
              class="flex items-center gap-1 text-xs transition-colors"
              :class="article.liked ? 'text-[#C41E3A]' : 'text-gray-400 hover:text-[#C41E3A]'"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" :fill="article.liked ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
              <span v-if="article.likes_count > 0">{{ article.likes_count }}</span>
            </button>
            <!-- Condividi articolo principale -->
            <button
              @click="shareMain"
              class="flex items-center gap-1 text-xs transition-colors"
              :class="article.shared ? 'text-[#C41E3A]' : 'text-gray-400 hover:text-gray-600'"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
              </svg>
              <span v-if="article.shares_count > 0">{{ article.shares_count }}</span>
            </button>
          </div>
        </div>

        <!-- ── Copertura mediale ── -->
        <div class="border-t border-gray-100 pt-3 mt-2" @click.stop>

          <!-- Più fonti: mostra ogni giornale con il suo titolo, raggruppato per orientamento -->
          <template v-if="hasCoverage">
            <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-3">
              Queste testate ne hanno parlato
            </p>

            <template v-for="lean in ['left','center-left','center','center-right','right','international','altro']" :key="lean">
              <div v-if="byLean[lean]?.length" class="mb-4">

                <!-- Badge orientamento + numero testate -->
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-xs font-bold px-2 py-0.5 rounded-full text-white" :class="leanBgClass(lean)">
                    {{ leanLabelFull(lean) }}
                  </span>
                  <span class="text-xs text-gray-400">
                    {{ byLean[lean].length }} {{ byLean[lean].length === 1 ? 'testata' : 'testate' }}
                  </span>
                </div>

                <!-- Lista: una riga per ogni giornale con il suo titolo + azioni -->
                <ul class="space-y-2">
                  <li v-for="src in byLean[lean]" :key="src.id">
                    <div
                      class="pl-3"
                      :style="{ borderLeft: '2px solid ' + leanBorderHex(lean) }"
                    >
                      <!-- Titolo cliccabile → apre il sito + traccia click -->
                      <a
                        :href="src.url"
                        target="_blank"
                        rel="noopener"
                        @click.stop="trackCoverageClick(src.id)"
                        class="group block hover:bg-gray-50 rounded-r transition-colors"
                      >
                        <span class="text-xs font-semibold text-gray-500 block uppercase tracking-wide mb-0.5">{{ src.source_name }}</span>
                        <span class="text-xs text-gray-700 group-hover:text-[#C41E3A] leading-snug line-clamp-2 block">{{ src.title }}</span>
                      </a>
                      <!-- Azioni FUORI dall'<a> → nessun conflitto di navigazione -->
                      <div class="flex items-center gap-3 mt-1" @click.stop>
                        <button
                          @click="toggleCoverageLike(src)"
                          class="flex items-center gap-1 text-xs transition-colors"
                          :class="src.liked ? 'text-[#C41E3A]' : 'text-gray-300 hover:text-[#C41E3A]'"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 pointer-events-none" :fill="src.liked ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                          </svg>
                          <span v-if="src.likes_count > 0">{{ src.likes_count }}</span>
                        </button>
                        <button
                          @click="shareCoverageArticle(src)"
                          class="flex items-center gap-1 text-xs transition-colors"
                          :class="src.shared ? 'text-[#C41E3A]' : 'text-gray-300 hover:text-gray-500'"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                          </svg>
                          <span v-if="src.shares_count > 0">{{ src.shares_count }}</span>
                        </button>
                      </div>
                    </div>
                  </li>
                </ul>

              </div>
            </template>
          </template>

          <!-- Fonte singola -->
          <div v-else class="flex flex-col gap-1.5">
            <p class="text-xs text-gray-400">Solo questa testata ha pubblicato la notizia</p>
            <div class="flex items-center gap-2">
              <span
                v-if="article.political_lean"
                class="text-xs font-bold px-2 py-0.5 rounded-full text-white"
                :class="leanBgClass(article.political_lean)"
              >{{ leanLabelFull(article.political_lean) }}</span>
              <span class="text-xs font-semibold text-gray-600">{{ article.source_name }}</span>
            </div>
          </div>

        </div>
      </div>

      <!-- Spacer -->
      <div class="flex-1"></div>

      <!-- ── Barra orientamenti con etichette e percentuali ── -->
      <div class="flex h-7 w-full overflow-hidden">
        <template v-for="lean in leanBarOrder" :key="lean">
          <div
            v-if="leanBarCounts[lean]"
            class="flex items-center justify-center overflow-hidden px-1 min-w-0"
            :style="{ flex: leanBarCounts[lean], backgroundColor: leanSolidHex[lean] }"
          >
            <span
              class="text-white font-bold whitespace-nowrap overflow-hidden text-ellipsis select-none"
              style="font-size:9px; text-shadow:0 1px 2px rgba(0,0,0,0.35);"
            >{{ leanBarShortLabel[lean] }} {{ leanBarPercent(lean) }}%</span>
          </div>
        </template>
      </div>
    </div>
  </article>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  article: { type: Object, required: true },
  compact: { type: Boolean, default: false },
  featured: { type: Boolean, default: false },
});

const emit = defineEmits(['click', 'like', 'share']);

// Decode HTML entities senza v-html (sicuro, no XSS)
function decodeHtml(str) {
  if (!str) return '';
  const el = document.createElement('textarea');
  el.innerHTML = str;
  return el.value;
}

// ── Badge lean in alto alla card ───────────────────────
const leanMap = {
  left:          { label: 'Sinistra',        class: 'badge-left' },
  'center-left': { label: 'Centro-sin.',     class: 'badge-center-left' },
  center:        { label: 'Centro',          class: 'badge-center' },
  'center-right':{ label: 'Centro-des.',     class: 'badge-center-right' },
  right:         { label: 'Destra',          class: 'badge-right' },
  international: { label: 'Int\'l',          class: 'badge-international' },
  altro:         { label: 'Media neutri',    class: 'badge-altro' },
};
const leanBadgeClass = computed(() => leanMap[props.article.political_lean]?.class ?? '');
const leanLabel      = computed(() => leanMap[props.article.political_lean]?.label ?? '');

const formattedDate = computed(() => {
  if (!props.article.published_at) return '';
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short' })
    .format(new Date(props.article.published_at));
});

// ── Coverage deduplicata ──────────────────────────────
const uniqueCoverage = computed(() => {
  const seen = new Set([props.article.source_domain]);
  return (props.article.coverage ?? []).filter(src => {
    if (seen.has(src.source_domain)) return false;
    seen.add(src.source_domain);
    return true;
  });
});

// Copia reattiva locale dei coverage items (per aggiornare like/share senza mutare props)
const coverageItems = ref([]);
const initCoverage = () => {
  coverageItems.value = uniqueCoverage.value.map(src => ({ ...src }));
};
initCoverage();

// Quando la prop cambia (nuova fetch), reinizializza
import { watch } from 'vue';
watch(() => props.article.coverage, initCoverage, { deep: true });

const hasCoverage = computed(() => coverageItems.value.length > 0);

// ── Colori per lean ──────────────────────────────────
const leanHex = {
  left:          '#1D4ED8',
  'center-left': '#60A5FA',
  center:        '#6B7280',
  'center-right':'#FB923C',
  right:         '#DC2626',
  international: '#166534',
  altro:         '#7C3AED',
};

// Raggruppa la coverage per orientamento
const byLean = computed(() => {
  const groups = { left: [], 'center-left': [], center: [], 'center-right': [], right: [], international: [], altro: [] };
  coverageItems.value.forEach(src => {
    const l = src.lean ?? 'altro';
    (groups[l] ?? groups.altro).push(src);
  });
  return groups;
});

// ── Barra coverage ────────────────────────────────────
const leanBarCounts = computed(() => {
  const counts = { left: 0, 'center-left': 0, center: 0, 'center-right': 0, right: 0, international: 0, altro: 0 };
  const mainLean = props.article.political_lean ?? 'altro';
  if (mainLean in counts) counts[mainLean] = 1; else counts.altro = 1;
  coverageItems.value.forEach(src => {
    const l = src.lean ?? 'altro';
    if (l in counts) counts[l]++; else counts.altro++;
  });
  return counts;
});
const leanBarOrder = ['left', 'center-left', 'center', 'center-right', 'right', 'international', 'altro'];
const leanSolidHex = leanHex;
const leanBarTotal = computed(() => Object.values(leanBarCounts.value).reduce((s, n) => s + n, 0));
function leanBarPercent(lean) {
  const t = leanBarTotal.value;
  return t ? Math.round((leanBarCounts.value[lean] ?? 0) / t * 100) : 0;
}
const leanBarShortLabel = {
  left:          'Sx',
  'center-left': 'C.Sx',
  center:        'Cen.',
  'center-right':'C.Dx',
  right:         'Dx',
  international: "Int'l",
  altro:         'Neu.',
};

// ── Etichette orientamento ────────────────────────────
const leanLabelMap = {
  left:          'Sinistra',
  'center-left': 'Centro-sinistra',
  center:        'Centro',
  'center-right':'Centro-destra',
  right:         'Destra',
  international: 'Internazionale',
  altro:         'Media neutri',
};
function leanLabelFull(lean) { return leanLabelMap[lean] ?? lean; }

const leanBgMap = {
  left:          'bg-blue-700',
  'center-left': 'bg-blue-400',
  center:        'bg-gray-500',
  'center-right':'bg-orange-400',
  right:         'bg-red-600',
  international: 'bg-green-800',
  altro:         'bg-purple-500',
};
function leanBgClass(lean) { return leanBgMap[lean] ?? 'bg-gray-400'; }
function leanBorderHex(lean) { return leanHex[lean] ?? '#D1D5DB'; }

// ── Azioni articolo principale ────────────────────────
async function shareMain() {
  // Apri share dialog nativo
  if (navigator.share) {
    navigator.share({ title: props.article.title, url: props.article.url }).catch(() => {});
  } else {
    navigator.clipboard?.writeText(props.article.url).then(() => alert('Link copiato!'));
  }
  // Traccia sul backend e aggiorna contatore via emit al parent
  emit('share', props.article);
}

// ── Click tracking ────────────────────────────────────
function trackCoverageClick(id) {
  axios.post(`/api/articles/${id}/click`).catch(() => {});
}

// ── Azioni coverage (gestite localmente) ─────────────
async function toggleCoverageLike(src) {
  try {
    const res = await axios.post(`/api/articles/${src.id}/like`);
    src.liked       = res.data.liked;
    src.likes_count = res.data.likes_count;
  } catch (e) { /* non autenticato o errore silenzioso */ }
}

async function shareCoverageArticle(src) {
  // Apri share dialog
  if (navigator.share) {
    navigator.share({ title: src.title, url: src.url }).catch(() => {});
  } else {
    navigator.clipboard?.writeText(src.url).then(() => alert('Link copiato!'));
  }
  // Traccia
  try {
    const res = await axios.post(`/api/articles/${src.id}/share`);
    src.shared        = res.data.shared;
    src.shares_count  = res.data.shares_count;
  } catch (e) { /* silenzioso */ }
}
</script>
