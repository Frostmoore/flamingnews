<template>
  <article
    class="group cursor-pointer bg-white border border-gray-200 hover:border-[#C41E3A] transition-colors duration-200 flex"
    :class="featured ? 'flex-row' : 'flex-col'"
    @click="$emit('click', article)"
  >
    <!-- Immagine -->
    <div
      v-if="article.url_to_image"
      class="overflow-hidden flex-shrink-0"
      :class="featured ? 'w-1/2 aspect-[4/3]' : 'aspect-[16/9] w-full'"
    >
      <img
        :src="article.url_to_image"
        :alt="decodeHtml(article.title)"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        loading="lazy"
      />
    </div>

    <div class="p-4 flex-1 flex flex-col">
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
        :class="featured ? 'text-2xl' : compact ? 'text-base' : 'text-lg'"
      >{{ decodeHtml(article.title) }}</h3>

      <!-- Descrizione -->
      <p v-if="!compact && article.description" class="text-sm text-gray-600 line-clamp-2 mb-3">
        {{ decodeHtml(article.description) }}
      </p>

      <!-- Footer: categoria + azioni -->
      <div class="flex items-center justify-between gap-2 mb-3">
        <span class="text-xs text-[#C41E3A] font-semibold uppercase tracking-wider">
          {{ article.category }}
        </span>
        <div class="flex items-center gap-3" @click.stop>
          <!-- Like -->
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
          <!-- Condividi -->
          <button @click="share" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
            </svg>
          </button>
        </div>
      </div>

      <!-- ── Copertura mediale ── -->
      <div class="border-t border-gray-100 pt-3 mt-auto" @click.stop>

        <!-- Più fonti: mostra ogni giornale con il suo titolo, raggruppato per orientamento -->
        <template v-if="hasCoverage">
          <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-3">
            Queste testate ne hanno parlato
          </p>

          <template v-for="lean in ['left','center','right','international','altro']" :key="lean">
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

              <!-- Lista: una riga per ogni giornale con il suo titolo -->
              <ul class="space-y-2">
                <li v-for="src in byLean[lean]" :key="src.id">
                  <a
                    :href="src.url"
                    target="_blank"
                    rel="noopener"
                    @click.stop
                    class="group block pl-3 hover:bg-gray-50 rounded-r transition-colors"
                    :style="{ borderLeft: '2px solid ' + leanBorderHex(lean) }"
                  >
                    <span class="text-xs font-semibold text-gray-500 block uppercase tracking-wide mb-0.5">{{ src.source_name }}</span>
                    <span class="text-xs text-gray-700 group-hover:text-[#C41E3A] leading-snug line-clamp-2 block">{{ src.title }}</span>
                  </a>
                </li>
              </ul>

            </div>
          </template>
        </template>

        <!-- Fonte singola: mostra orientamento del giornale chiaramente -->
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

    <!-- ── Barra orientamenti ── -->
    <div class="flex h-3 w-full overflow-hidden">
      <template v-for="lean in leanBarOrder" :key="lean">
        <div
          v-if="leanBarCounts[lean]"
          :style="{ flex: leanBarCounts[lean], backgroundColor: leanSolidHex[lean] }"
          :title="leanLabelFull(lean) + ': ' + leanBarCounts[lean]"
        ></div>
      </template>
    </div>
  </article>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  article: { type: Object, required: true },
  compact: { type: Boolean, default: false },
  featured: { type: Boolean, default: false },
});

defineEmits(['click', 'like']);

const showLeanDetail = ref(false);

// Decode HTML entities senza v-html (sicuro, no XSS)
function decodeHtml(str) {
  if (!str) return '';
  const el = document.createElement('textarea');
  el.innerHTML = str;
  return el.value;
}

// ── Badge lean in alto alla card ───────────────────────
const leanMap = {
  left:          { label: 'Sinistra',       class: 'badge-left' },
  right:         { label: 'Destra',         class: 'badge-right' },
  center:        { label: 'Centro',         class: 'badge-center' },
  international: { label: 'Int\'l',         class: 'badge-international' },
  altro:         { label: 'Media neutri',   class: 'badge-altro' },
};
const leanBadgeClass = computed(() => leanMap[props.article.political_lean]?.class ?? '');
const leanLabel      = computed(() => leanMap[props.article.political_lean]?.label ?? '');

const formattedDate = computed(() => {
  if (!props.article.published_at) return '';
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short' })
    .format(new Date(props.article.published_at));
});

// ── Coverage ──────────────────────────────────────────
const hasCoverage = computed(() => props.article.coverage?.length > 0);

// Raggruppa la coverage per orientamento (solo le altre testate, non l'articolo corrente)
const byLean = computed(() => {
  const groups = { left: [], center: [], right: [], international: [], altro: [] };
  (props.article.coverage ?? []).forEach(src => {
    const l = src.lean ?? 'altro';
    (groups[l] ?? groups.altro).push(src);
  });
  return groups;
});

// ── Barra coverage (include articolo principale + coverage) ───────────────
const leanBarCounts = computed(() => {
  const counts = { left: 0, center: 0, right: 0, international: 0, altro: 0 };
  const mainLean = props.article.political_lean ?? 'altro';
  counts[mainLean] = 1;
  (props.article.coverage ?? []).forEach(src => {
    const l = src.lean ?? 'altro';
    if (l in counts) counts[l]++;
    else counts.altro++;
  });
  return counts;
});
const leanBarOrder = ['left', 'center', 'right', 'international', 'altro'];
const leanSolidHex = {
  left:          '#2563EB',
  center:        '#6B7280',
  right:         '#DC2626',
  international: '#D97706',
  altro:         '#7C3AED',
};

// ── Etichette orientamento ────────────────────────────
const leanLabelMap = {
  left:          'Sinistra',
  center:        'Centro',
  right:         'Destra',
  international: 'Internazionale',
  altro:         'Media neutri',
};
function leanLabelFull(lean) { return leanLabelMap[lean] ?? lean; }

// Classi Tailwind per badge colorato (sfondo pieno)
const leanBgMap = {
  left:          'bg-blue-600',
  center:        'bg-gray-500',
  right:         'bg-red-600',
  international: 'bg-amber-500',
  altro:         'bg-purple-500',
};
function leanBgClass(lean) { return leanBgMap[lean] ?? 'bg-gray-400'; }

// Colore bordo sinistro per i titoli (hex diretto, evita purge Tailwind)
const leanBorderHexMap = {
  left:          '#3B82F6',
  center:        '#9CA3AF',
  right:         '#EF4444',
  international: '#F59E0B',
  altro:         '#A855F7',
};
function leanBorderHex(lean) { return leanBorderHexMap[lean] ?? '#D1D5DB'; }

// ── Condividi ─────────────────────────────────────────
function share() {
  if (navigator.share) {
    navigator.share({ title: props.article.title, url: props.article.url }).catch(() => {});
  } else {
    navigator.clipboard?.writeText(props.article.url).then(() => alert('Link copiato!'));
  }
}
</script>
