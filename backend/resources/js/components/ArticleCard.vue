<template>
  <article
    class="group cursor-pointer bg-white border border-gray-200 hover:border-[#C41E3A] transition-colors duration-200"
    @click="$emit('click', article)"
  >
    <!-- Immagine -->
    <div v-if="article.url_to_image" class="overflow-hidden aspect-[16/9]">
      <img
        :src="article.url_to_image"
        :alt="decodeHtml(article.title)"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        loading="lazy"
      />
    </div>

    <div class="p-4">
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
        :class="compact ? 'text-base' : 'text-lg'"
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

      <!-- ── Coverage dashboard (solo se ci sono altre fonti) ── -->
      <template v-if="hasCoverage" @click.stop>
        <div class="border-t border-gray-100 pt-3 mt-1" @click.stop>

          <!-- Barra lean spectrum + contatori -->
          <button
            class="w-full text-left"
            @click.stop="showLeanDetail = !showLeanDetail"
          >
            <!-- Label + toggle -->
            <div class="flex items-center justify-between mb-1.5">
              <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                Copertura media ({{ article.coverage.length + 1 }} fonti)
              </span>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-3.5 h-3.5 text-gray-400 transition-transform"
                :class="showLeanDetail ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
              </svg>
            </div>

            <!-- Barra proporzionale -->
            <div class="flex h-2 rounded-full overflow-hidden gap-px">
              <div
                v-if="leanCounts.left"
                class="bg-blue-500 transition-all"
                :style="{ width: leanPct('left') + '%' }"
              ></div>
              <div
                v-if="leanCounts.center"
                class="bg-gray-400 transition-all"
                :style="{ width: leanPct('center') + '%' }"
              ></div>
              <div
                v-if="leanCounts.international"
                class="bg-amber-400 transition-all"
                :style="{ width: leanPct('international') + '%' }"
              ></div>
              <div
                v-if="leanCounts.right"
                class="bg-red-500 transition-all"
                :style="{ width: leanPct('right') + '%' }"
              ></div>
              <div
                v-if="leanCounts.altro"
                class="bg-purple-400 transition-all"
                :style="{ width: leanPct('altro') + '%' }"
              ></div>
            </div>

            <!-- Legenda contatori -->
            <div class="flex gap-3 mt-1.5 flex-wrap">
              <span v-if="leanCounts.left" class="flex items-center gap-1 text-xs text-blue-600">
                <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                Sinistra {{ leanCounts.left }}
              </span>
              <span v-if="leanCounts.center" class="flex items-center gap-1 text-xs text-gray-500">
                <span class="w-2 h-2 rounded-full bg-gray-400 inline-block"></span>
                Centro {{ leanCounts.center }}
              </span>
              <span v-if="leanCounts.right" class="flex items-center gap-1 text-xs text-red-600">
                <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                Destra {{ leanCounts.right }}
              </span>
              <span v-if="leanCounts.international" class="flex items-center gap-1 text-xs text-amber-600">
                <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>
                Int'l {{ leanCounts.international }}
              </span>
              <span v-if="leanCounts.altro" class="flex items-center gap-1 text-xs text-purple-600">
                <span class="w-2 h-2 rounded-full bg-purple-400 inline-block"></span>
                Altro {{ leanCounts.altro }}
              </span>
            </div>
          </button>

          <!-- Fonti in piccolo -->
          <div class="flex flex-wrap gap-1.5 mt-2">
            <a
              v-for="src in article.coverage"
              :key="src.id"
              :href="src.url"
              target="_blank"
              rel="noopener"
              @click.stop
              class="inline-flex items-center gap-1 text-xs px-2 py-0.5 border rounded-full hover:bg-gray-50 transition-colors"
              :class="coverageBorderClass(src.lean)"
              :title="src.title"
            >
              <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :class="coverageDotClass(src.lean)"></span>
              {{ src.source_name ?? src.source_domain }}
            </a>
          </div>

          <!-- Dettaglio titoli per orientamento (espandibile) -->
          <div v-if="showLeanDetail" class="mt-3 space-y-3" @click.stop>
            <template v-for="lean in ['left','center','right','international','altro']" :key="lean">
              <div v-if="byLean[lean]?.length">
                <p class="text-xs font-bold uppercase tracking-wide mb-1.5" :class="leanTitleClass(lean)">
                  {{ leanLabelFull(lean) }}
                </p>
                <ul class="space-y-1">
                  <li v-for="src in byLean[lean]" :key="src.id">
                    <a
                      :href="src.url"
                      target="_blank"
                      rel="noopener"
                      @click.stop
                      class="text-xs text-gray-700 hover:text-[#C41E3A] leading-snug line-clamp-2 block"
                    >{{ src.title }}</a>
                    <span class="text-xs text-gray-400">{{ src.source_name }}</span>
                  </li>
                </ul>
              </div>
            </template>
          </div>
        </div>
      </template>

      <!-- Nessuna coverage ma orientamento noto -->
      <div v-else class="border-t border-gray-100 pt-2 mt-1 flex items-center gap-2">
        <span
          v-if="article.political_lean"
          class="text-xs font-semibold px-2 py-0.5 rounded-full uppercase tracking-wide"
          :class="leanBadgeClass"
        >{{ leanLabel }}</span>
        <span class="text-xs text-gray-300">Solo questa fonte</span>
      </div>
    </div>
  </article>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  article: { type: Object, required: true },
  compact: { type: Boolean, default: false },
});

defineEmits(['click', 'like']);

const showLeanDetail = ref(false);

// Decode HTML entities in-browser senza v-html (sicuro, no XSS)
function decodeHtml(str) {
  if (!str) return '';
  const el = document.createElement('textarea');
  el.innerHTML = str;
  return el.value;
}

// ── Lean badge principale ──────────────────────────────
const leanMap = {
  left:          { label: 'Sinistra',      class: 'badge-left' },
  right:         { label: 'Destra',        class: 'badge-right' },
  center:        { label: 'Centro',        class: 'badge-center' },
  international: { label: 'Int\'l',        class: 'badge-international' },
  altro:         { label: 'Altro',         class: 'badge-altro' },
};
const leanBadgeClass = computed(() => leanMap[props.article.political_lean]?.class ?? 'badge-center');
const leanLabel      = computed(() => leanMap[props.article.political_lean]?.label ?? '');

const formattedDate = computed(() => {
  if (!props.article.published_at) return '';
  return new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short' })
    .format(new Date(props.article.published_at));
});

// ── Coverage ──────────────────────────────────────────
const hasCoverage = computed(() => props.article.coverage?.length > 0);

// Raggruppa coverage per orientamento (include anche la fonte principale)
const allSources = computed(() => {
  const self = {
    id: -1,
    title: props.article.title,
    source_name: props.article.source_name,
    source_domain: props.article.source_domain,
    url: props.article.url,
    lean: props.article.political_lean,
  };
  return [self, ...(props.article.coverage ?? [])];
});

const byLean = computed(() => {
  const groups = { left: [], center: [], right: [], international: [], altro: [] };
  allSources.value.forEach(src => {
    const l = src.lean ?? 'altro';
    if (groups[l]) groups[l].push(src);
    else groups.altro.push(src);
  });
  return groups;
});

const leanCounts = computed(() => ({
  left:          byLean.value.left.length,
  center:        byLean.value.center.length,
  right:         byLean.value.right.length,
  international: byLean.value.international.length,
  altro:         byLean.value.altro.length,
}));

const total = computed(() => allSources.value.length);

function leanPct(lean) {
  return total.value ? (leanCounts.value[lean] / total.value) * 100 : 0;
}

// ── Colori coverage chips ─────────────────────────────
const leanColors = {
  left:          { border: 'border-blue-300',   dot: 'bg-blue-500' },
  right:         { border: 'border-red-300',    dot: 'bg-red-500' },
  center:        { border: 'border-gray-300',   dot: 'bg-gray-400' },
  international: { border: 'border-amber-300',  dot: 'bg-amber-500' },
  altro:         { border: 'border-purple-300', dot: 'bg-purple-400' },
};
function coverageBorderClass(lean) { return leanColors[lean]?.border ?? 'border-gray-200'; }
function coverageDotClass(lean)   { return leanColors[lean]?.dot   ?? 'bg-gray-300'; }

const leanTitleColors = {
  left:          'text-blue-700',
  right:         'text-red-700',
  center:        'text-gray-600',
  international: 'text-amber-700',
  altro:         'text-purple-700',
};
function leanTitleClass(lean) { return leanTitleColors[lean] ?? 'text-gray-600'; }

const leanLabelMap = { left: 'Sinistra', center: 'Centro', right: 'Destra', international: 'Internazionale', altro: 'Altro' };
function leanLabelFull(lean) { return leanLabelMap[lean] ?? lean; }

// ── Condividi ─────────────────────────────────────────
function share() {
  if (navigator.share) {
    navigator.share({ title: props.article.title, url: props.article.url }).catch(() => {});
  } else {
    navigator.clipboard?.writeText(props.article.url).then(() => alert('Link copiato!'));
  }
}
</script>
