<template>
  <div class="min-h-screen bg-[#F8F6F1]">

    <!-- Header -->
    <header class="sticky top-0 z-30 bg-white shadow-sm">
      <div class="bg-[#C41E3A] h-1 w-full"></div>
      <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between border-b border-gray-100">
        <a href="/" class="font-display text-2xl font-bold text-[#1A1A1A] tracking-tight">
          Flaming<span class="text-[#C41E3A]">News</span>
        </a>
        <span class="text-sm font-semibold text-gray-400 uppercase tracking-widest">Analytics</span>
      </div>
    </header>

    <!-- Loading -->
    <div v-if="loading" class="max-w-7xl mx-auto px-4 py-16 text-center text-gray-400">
      <svg class="animate-spin w-8 h-8 mx-auto text-[#C41E3A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
      </svg>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="max-w-7xl mx-auto px-4 py-16 text-center text-red-600">
      <p class="font-semibold">Errore nel caricamento dei dati</p>
      <button @click="load" class="mt-4 px-4 py-2 bg-[#C41E3A] text-white text-sm">Riprova</button>
    </div>

    <template v-else>
      <div class="max-w-7xl mx-auto px-4 py-8 space-y-12">

        <!-- Totali -->
        <section>
          <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 border-b border-gray-200 pb-2">Panoramica</h2>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mt-4">
            <StatCard label="Articoli" :value="fmt(data.totals.articles)" />
            <StatCard label="Topic"    :value="fmt(data.totals.topics)" />
            <StatCard label="Testate"  :value="fmt(data.totals.sources)" />
            <StatCard label="Click"    :value="fmt(data.totals.clicks)"  accent />
            <StatCard label="Like"     :value="fmt(data.totals.likes)"   accent />
            <StatCard label="Share"    :value="fmt(data.totals.shares)"  accent />
          </div>
        </section>

        <!-- Articoli -->
        <section>
          <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 border-b border-gray-200 pb-2">Articoli</h2>
          <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6 mt-4">
            <TopList
              title="Più cliccati"
              icon="👆"
              :items="top(data.articles.top_clicked, 10)"
              label-key="clicks_count"
              label-suffix="click"
            />
            <TopList
              title="Più piaciuti"
              icon="❤️"
              :items="top(data.articles.top_liked, 10)"
              label-key="likes_count"
              label-suffix="like"
            />
            <TopList
              title="Più condivisi"
              icon="↗️"
              :items="top(data.articles.top_shared, 10)"
              label-key="shares_count"
              label-suffix="share"
            />
            <TopList
              title="Più coperti"
              icon="📰"
              :items="top(data.articles.top_covered, 10)"
              label-key="coverage_count"
              label-suffix="testate"
            />
          </div>
        </section>

        <!-- Testate -->
        <section>
          <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 border-b border-gray-200 pb-2">Testate</h2>
          <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6 mt-4">
            <TopList
              title="Più produttive"
              icon="📄"
              :items="top(data.sources.by_articles, 10)"
              name-key="source_name"
              label-key="articles_count"
              label-suffix="articoli"
            />
            <TopList
              title="Più cliccate"
              icon="👆"
              :items="top(data.sources.by_clicks, 10)"
              name-key="source_name"
              label-key="clicks_count"
              label-suffix="click"
            />
            <TopList
              title="Più piaciute"
              icon="❤️"
              :items="top(data.sources.by_likes, 10)"
              name-key="source_name"
              label-key="likes_count"
              label-suffix="like"
            />
            <TopList
              title="Engagement rate"
              icon="📈"
              :items="top(data.sources.by_engagement_rate, 10)"
              name-key="source_name"
              label-key="engagement_rate"
              label-suffix="like+share/art."
            />
          </div>
        </section>

        <!-- Orientamento politico -->
        <section>
          <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 border-b border-gray-200 pb-2">Orientamento politico</h2>
          <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6 mt-4">
            <LeanChart title="Articoli pubblicati"  :data="data.political_lean.by_articles" value-key="articles_count" />
            <LeanChart title="Click ricevuti"       :data="data.political_lean.by_clicks"   value-key="clicks_count" />
            <LeanChart title="Like ricevuti"        :data="data.political_lean.by_likes"    value-key="likes_count" />
            <LeanChart title="Like rate (per art.)" :data="data.political_lean.like_rate"   value-key="like_rate" />
          </div>
        </section>

        <!-- Categorie -->
        <section>
          <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 border-b border-gray-200 pb-2">Categorie</h2>
          <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6 mt-4">
            <TopList
              title="Per volume"
              icon="📋"
              :items="top(data.categories.by_articles, 10)"
              name-key="category"
              label-key="articles_count"
              label-suffix="articoli"
            />
            <TopList
              title="Per click"
              icon="👆"
              :items="top(data.categories.by_clicks, 10)"
              name-key="category"
              label-key="clicks_count"
              label-suffix="click"
            />
            <TopList
              title="Per like"
              icon="❤️"
              :items="top(data.categories.by_likes, 10)"
              name-key="category"
              label-key="likes_count"
              label-suffix="like"
            />
            <TopList
              title="Per share"
              icon="↗️"
              :items="top(data.categories.by_shares, 10)"
              name-key="category"
              label-key="shares_count"
              label-suffix="share"
            />
          </div>
        </section>

        <!-- API access callout -->
        <section class="border border-[#C41E3A]/30 bg-white p-6 flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="flex-1">
            <p class="text-xs font-bold uppercase tracking-widest text-[#C41E3A] mb-1">API Analytics</p>
            <p class="text-sm text-gray-600">
              Questa pagina mostra solo i top 10. L'endpoint
              <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs font-mono">GET /api/analytics</code>
              restituisce dati completi (top 50 per ogni metrica, engagement rate, like rate per orientamento e molto altro).
            </p>
          </div>
          <a
            href="mailto:nbdy88@gmail.com?subject=Accesso API FlamingNews Analytics"
            class="flex-shrink-0 px-5 py-2.5 bg-[#C41E3A] text-white text-xs font-bold uppercase tracking-wide hover:bg-[#a01830] transition-colors whitespace-nowrap"
          >
            Richiedi accesso
          </a>
        </section>

        <p class="text-center text-xs text-gray-300 pb-4">
          Dati aggiornati al {{ generatedAt }}
        </p>

      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// ── Sub-components ────────────────────────────────────────────────────────────

const StatCard = {
  props: { label: String, value: [String, Number], accent: Boolean },
  template: `
    <div class="bg-white border border-gray-100 p-4 text-center">
      <div class="text-2xl font-bold" :class="accent ? 'text-[#C41E3A]' : 'text-[#1A1A1A]'">{{ value }}</div>
      <div class="text-xs text-gray-400 uppercase tracking-wide mt-1">{{ label }}</div>
    </div>
  `,
};

const TopList = {
  props: {
    title:       String,
    icon:        String,
    items:       Array,
    nameKey:     { type: String, default: 'title' },
    labelKey:    String,
    labelSuffix: String,
  },
  template: `
    <div class="bg-white border border-gray-100 p-4">
      <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-3">
        <span class="mr-1">{{ icon }}</span>{{ title }}
      </h3>
      <ol class="space-y-2">
        <li v-for="(item, i) in items" :key="i" class="flex items-start gap-2 text-sm">
          <span class="flex-shrink-0 w-5 text-right text-gray-300 font-mono text-xs pt-0.5">{{ i + 1 }}</span>
          <div class="flex-1 min-w-0">
            <p class="truncate text-[#1A1A1A] font-medium leading-tight">{{ item[nameKey] }}</p>
            <p class="text-xs text-[#C41E3A] font-semibold mt-0.5">
              {{ item[labelKey] }} <span class="text-gray-400 font-normal">{{ labelSuffix }}</span>
            </p>
          </div>
        </li>
        <li v-if="!items.length" class="text-xs text-gray-300 italic">Nessun dato</li>
      </ol>
    </div>
  `,
};

const leanMeta = {
  'left':          { label: 'Sinistra',        color: '#1D4ED8' },
  'center-left':   { label: 'Centro-sinistra', color: '#60A5FA' },
  'center':        { label: 'Centro',          color: '#6B7280' },
  'center-right':  { label: 'Centro-destra',   color: '#FB923C' },
  'right':         { label: 'Destra',          color: '#DC2626' },
  'international': { label: 'Internazionale',  color: '#D97706' },
  'altro':         { label: 'Neutri',          color: '#7C3AED' },
};

const LeanChart = {
  props: { title: String, data: Array, valueKey: String },
  computed: {
    total() { return this.data.reduce((s, r) => s + Number(r[this.valueKey]), 0); },
    rows()  { return this.data.filter(r => Number(r[this.valueKey]) > 0); },
  },
  methods: {
    meta(lean) { return leanMeta[lean] ?? { label: lean, color: '#9CA3AF' }; },
    pct(val)   { return this.total > 0 ? Math.round(Number(val) / this.total * 100) : 0; },
    fmt(n)     { return Number(n).toLocaleString('it-IT'); },
  },
  template: `
    <div class="bg-white border border-gray-100 p-4">
      <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-3">{{ title }}</h3>
      <div v-if="!rows.length" class="text-xs text-gray-300 italic">Nessun dato</div>
      <template v-else>
        <!-- Barra stacked -->
        <div class="flex h-3 rounded overflow-hidden mb-4">
          <div
            v-for="row in rows" :key="row.political_lean"
            :style="{ width: pct(row[valueKey]) + '%', background: meta(row.political_lean).color }"
            :title="meta(row.political_lean).label + ': ' + pct(row[valueKey]) + '%'"
          ></div>
        </div>
        <!-- Lista -->
        <ol class="space-y-2">
          <li v-for="(row, i) in rows" :key="i" class="flex items-center gap-2 text-sm">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ background: meta(row.political_lean).color }"></span>
            <span class="flex-1 text-xs text-[#1A1A1A]">{{ meta(row.political_lean).label }}</span>
            <span class="text-xs font-semibold text-[#C41E3A]">{{ fmt(row[valueKey]) }}</span>
            <span class="text-xs text-gray-300 w-8 text-right">{{ pct(row[valueKey]) }}%</span>
          </li>
        </ol>
      </template>
    </div>
  `,
};

// ── Main component ────────────────────────────────────────────────────────────

const data      = ref(null);
const loading   = ref(true);
const error     = ref(false);

const generatedAt = computed(() => {
  if (!data.value?.generated_at) return '';
  return new Date(data.value.generated_at).toLocaleString('it-IT');
});

function top(arr, n) { return (arr ?? []).slice(0, n); }

function fmt(n) { return Number(n).toLocaleString('it-IT'); }

async function load() {
  loading.value = true;
  error.value   = false;
  try {
    const res = await axios.get('/api/analytics');
    data.value = res.data;
  } catch {
    error.value = true;
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>

