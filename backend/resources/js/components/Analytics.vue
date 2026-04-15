<template>
  <div class="min-h-screen bg-[#F0EDE8]">

    <!-- ── Header ──────────────────────────────────────────────────────────── -->
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md shadow-sm">
      <div class="bg-gradient-to-r from-[#C41E3A] to-[#8B0000] h-1 w-full"></div>
      <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="/" class="font-display text-2xl font-bold text-[#1A1A1A] tracking-tight">
          Flaming<span class="text-[#C41E3A]">News</span>
        </a>
        <a href="/" class="text-xs text-gray-400 hover:text-[#C41E3A] transition-colors uppercase tracking-widest">← Feed</a>
      </div>

      <!-- Filtro categorie — pill style -->
      <div class="bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4">
          <div class="flex overflow-x-auto py-2 gap-2" style="scrollbar-width:none">
            <button
              v-for="cat in categories" :key="cat.value ?? 'all'"
              @click="selectCategory(cat.value)"
              class="flex-shrink-0 px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all duration-200"
              :class="activeCategory === cat.value
                ? 'bg-[#C41E3A] text-white shadow-md shadow-[#C41E3A]/30'
                : 'bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-[#1A1A1A]'"
            >{{ cat.label }}</button>
          </div>
        </div>
      </div>
    </header>

    <!-- ── Loading ─────────────────────────────────────────────────────────── -->
    <div v-if="loading" class="flex justify-center py-32">
      <svg class="animate-spin w-8 h-8 text-[#C41E3A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
      </svg>
    </div>

    <!-- ── Error ───────────────────────────────────────────────────────────── -->
    <div v-else-if="error" class="flex flex-col items-center py-32 gap-4">
      <p class="text-gray-500">Errore nel caricamento</p>
      <button @click="load" class="px-5 py-2 bg-[#C41E3A] text-white text-sm font-bold rounded-full shadow-lg shadow-[#C41E3A]/30">Riprova</button>
    </div>

    <div v-else class="max-w-7xl mx-auto px-4 py-10 space-y-16">

      <!-- ── Totali ───────────────────────────────────────────────────────── -->
      <section class="grid grid-cols-3 sm:grid-cols-6 gap-3">
        <div v-for="s in totalsCards" :key="s.label"
             class="bg-white rounded-2xl p-5 text-center shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
          <div class="font-display font-bold leading-none"
               :class="s.accent ? 'text-[#C41E3A] text-3xl' : 'text-[#1A1A1A] text-2xl'">{{ s.value }}</div>
          <div class="text-[10px] text-gray-400 uppercase tracking-widest mt-2">{{ s.label }}</div>
        </div>
      </section>

      <!-- ── Testate ──────────────────────────────────────────────────────── -->
      <section>
        <SectionTitle>Testate</SectionTitle>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
          <StackedRank title="Più produttive"
            :rows="top(data.sources.by_articles, 10)"
            name-key="source_name" value-key="articles_count" suffix="art."
            :clickable="true" @item-click="openSourceModal" />
          <StackedRank title="Più cliccate"
            :rows="top(data.sources.by_clicks, 10)"
            name-key="source_name" value-key="clicks_count" suffix="click"
            empty="Nessun click ancora"
            :clickable="true" @item-click="openSourceModal" />
          <StackedRank title="Più piaciute"
            :rows="top(data.sources.by_likes, 10)"
            name-key="source_name" value-key="likes_count" suffix="like"
            empty="Nessun like ancora"
            :clickable="true" @item-click="openSourceModal" />
          <StackedRank title="Più condivise"
            :rows="top(data.sources.by_shares, 10)"
            name-key="source_name" value-key="shares_count" suffix="share"
            empty="Nessun share ancora"
            :clickable="true" @item-click="openSourceModal" />
        </div>
      </section>

      <!-- ── Articoli ─────────────────────────────────────────────────────── -->
      <section>
        <SectionTitle>Articoli</SectionTitle>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
          <StackedRank title="Più coperti"
            :rows="top(data.articles.top_covered, 10)"
            name-key="title" value-key="coverage_count" suffix="testate"
            :long-name="true" empty="Nessun topic ancora"
            :clickable="true" @item-click="openCoverageModal" />
          <StackedRank title="Più cliccati"
            :rows="top(data.articles.top_clicked, 10)"
            name-key="title" value-key="clicks_count" suffix="click"
            :long-name="true" empty="Nessun click ancora"
            :clickable="true" @item-click="openCoverageModal" />
          <StackedRank title="Più piaciuti"
            :rows="top(data.articles.top_liked, 10)"
            name-key="title" value-key="likes_count" suffix="like"
            :long-name="true" empty="Nessun like ancora"
            :clickable="true" @item-click="openCoverageModal" />
          <StackedRank title="Più condivisi"
            :rows="top(data.articles.top_shared, 10)"
            name-key="title" value-key="shares_count" suffix="share"
            :long-name="true" empty="Nessun share ancora"
            :clickable="true" @item-click="openCoverageModal" />
        </div>
      </section>

      <!-- ── Orientamento politico ──────────────────────────────────────────── -->
      <section>
        <SectionTitle>Orientamento politico</SectionTitle>

        <!-- Barra stacked arrotondata -->
        <div class="flex h-10 rounded-2xl overflow-hidden mt-6 mb-6 shadow-md">
          <div v-for="(row, i) in data.political_lean.by_articles" :key="row.political_lean"
               class="flex items-center justify-center overflow-hidden transition-all duration-700"
               :style="{ width: leanPct(row.articles_count, leanTotal(data.political_lean.by_articles, 'articles_count')) + '%', background: leanColor(row.political_lean) }"
               :title="leanLabel(row.political_lean)">
            <span class="text-white text-xs font-bold truncate px-1 select-none"
                  style="text-shadow: 0 1px 3px rgba(0,0,0,.4)">
              {{ leanPct(row.articles_count, leanTotal(data.political_lean.by_articles, 'articles_count')) > 6 ? leanShort(row.political_lean) : '' }}
            </span>
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-5">Articoli pubblicati</p>
            <div class="space-y-4">
              <div v-for="row in data.political_lean.by_articles" :key="row.political_lean">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full shadow-sm" :style="{ background: leanColor(row.political_lean) }"></span>
                    <span class="text-sm font-medium text-[#1A1A1A]">{{ leanLabel(row.political_lean) }}</span>
                  </div>
                  <span class="text-sm font-bold text-[#1A1A1A]">{{ fmtN(row.articles_count) }}</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                  <div class="h-full rounded-full transition-all duration-700"
                       :style="{ width: leanPct(row.articles_count, leanTotal(data.political_lean.by_articles, 'articles_count')) + '%', background: leanColor(row.political_lean) }"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-2xl shadow-sm p-6">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-5">Like rate (like per articolo)</p>
            <div class="space-y-4">
              <div v-for="row in data.political_lean.like_rate" :key="row.political_lean">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full shadow-sm" :style="{ background: leanColor(row.political_lean) }"></span>
                    <span class="text-sm font-medium text-[#1A1A1A]">{{ leanLabel(row.political_lean) }}</span>
                  </div>
                  <span class="text-sm font-bold text-[#C41E3A]">{{ row.like_rate }}</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                  <div class="h-full rounded-full transition-all duration-700"
                       :style="{ width: leanPct(row.like_rate, leanTotal(data.political_lean.like_rate, 'like_rate')) + '%', background: leanColor(row.political_lean) }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── Categorie ──────────────────────────────────────────────────────── -->
      <section v-if="!activeCategory">
        <SectionTitle>Categorie</SectionTitle>
        <div class="mt-6 bg-white rounded-2xl shadow-sm p-6">
          <div class="space-y-3.5">
            <div v-for="(row, i) in top(data.categories.by_articles, 12)" :key="i">
              <div class="flex items-center justify-between mb-1.5">
                <span class="text-sm font-medium text-[#1A1A1A] capitalize">{{ row.category }}</span>
                <span class="text-sm font-bold text-[#1A1A1A]">{{ fmtN(row.articles_count) }}</span>
              </div>
              <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-700"
                     :style="{ width: (row.articles_count / data.categories.by_articles[0].articles_count * 100) + '%', background: `rgba(196,30,58,${Math.max(0.2, 1 - i * 0.07)})` }"></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── API callout ─────────────────────────────────────────────────────── -->
      <section class="rounded-2xl overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-[#C41E3A] to-[#8B0000] p-1"></div>
        <div class="bg-white p-6 flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="flex-1">
            <p class="text-[10px] font-bold uppercase tracking-widest text-[#C41E3A] mb-1">API Analytics — Accesso completo</p>
            <p class="text-sm text-gray-500 leading-relaxed">
              Questa pagina mostra solo i top 10. L'endpoint
              <code class="bg-gray-100 px-1.5 py-0.5 rounded-md text-xs font-mono">GET /api/analytics</code>
              accetta <code class="bg-gray-100 px-1.5 py-0.5 rounded-md text-xs font-mono">?category=</code>
              e restituisce top 50 per ogni metrica.
            </p>
          </div>
          <a href="mailto:nbdy88@gmail.com?subject=Accesso API FlamingNews Analytics"
             class="flex-shrink-0 px-6 py-2.5 bg-[#C41E3A] text-white text-xs font-bold uppercase tracking-widest rounded-full shadow-lg shadow-[#C41E3A]/30 hover:bg-[#a01830] transition-all hover:shadow-xl hover:-translate-y-0.5 whitespace-nowrap">
            Richiedi accesso
          </a>
        </div>
      </section>

      <p class="text-center text-xs text-gray-400 pb-4">Dati aggiornati al {{ generatedAt }}</p>
    </div>
  </div>

  <!-- ── Modale ─────────────────────────────────────────────────────────────── -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="modal.open"
           class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
           @keydown.esc.window="closeModal">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>

        <!-- Pannello -->
        <div class="relative bg-white w-full sm:max-w-lg sm:rounded-2xl rounded-t-2xl shadow-2xl flex flex-col"
             style="max-height: 85vh">
          <!-- Header sticky -->
          <div class="flex items-start justify-between px-5 py-4 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl z-10">
            <div class="flex-1 pr-4">
              <p v-if="modal.type === 'source'" class="text-[10px] font-bold uppercase tracking-widest text-[#C41E3A] mb-0.5">Testata</p>
              <p v-else class="text-[10px] font-bold uppercase tracking-widest text-[#C41E3A] mb-0.5">Coverage</p>
              <h3 class="font-bold text-[15px] text-[#1A1A1A] leading-snug">{{ modal.title }}</h3>
            </div>
            <button @click="closeModal"
                    class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 hover:text-[#1A1A1A] transition-colors text-lg leading-none">×</button>
          </div>

          <!-- Loading -->
          <div v-if="modal.loading" class="flex justify-center items-center py-16">
            <svg class="animate-spin w-7 h-7 text-[#C41E3A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
          </div>

          <!-- Contenuto: testata -->
          <div v-else-if="modal.type === 'source' && modal.data" class="overflow-y-auto p-5 space-y-5">
            <!-- Domain + orientamento -->
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-xs font-mono text-gray-400">{{ modal.data.source?.domain }}</span>
              <span v-if="modal.data.source?.political_lean"
                    class="px-2 py-0.5 rounded-full text-[11px] font-bold text-white"
                    :style="{ background: leanColor(modal.data.source.political_lean) }">
                {{ leanLabel(modal.data.source.political_lean) }}
              </span>
            </div>

            <!-- Totali -->
            <div class="grid grid-cols-4 gap-2">
              <div v-for="s in sourceModalCards" :key="s.label"
                   class="bg-gray-50 rounded-xl p-3 text-center">
                <div class="font-bold text-base" :class="s.accent ? 'text-[#C41E3A]' : 'text-[#1A1A1A]'">{{ s.value }}</div>
                <div class="text-[9px] text-gray-400 uppercase tracking-wider mt-1">{{ s.label }}</div>
              </div>
            </div>

            <!-- Top articoli -->
            <div v-if="modal.data.top_articles?.length">
              <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Articoli più cliccati</p>
              <div class="space-y-1.5">
                <a v-for="a in modal.data.top_articles" :key="a.id"
                   :href="a.url" target="_blank" rel="noopener noreferrer"
                   class="flex items-start gap-2 p-2.5 rounded-xl bg-gray-50 hover:bg-[#fff5f5] border border-transparent hover:border-[#f5c0c8] transition-all group">
                  <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-medium text-[#1A1A1A] group-hover:text-[#C41E3A] transition-colors leading-snug line-clamp-2">{{ a.title }}</p>
                    <div class="flex gap-2 mt-1">
                      <span class="text-[10px] text-gray-400 capitalize">{{ a.category }}</span>
                      <span class="text-[10px] text-gray-300">·</span>
                      <span class="text-[10px] text-gray-400">{{ fmtDate(a.published_at) }}</span>
                    </div>
                  </div>
                  <div class="flex flex-col items-end gap-0.5 flex-shrink-0">
                    <span v-if="a.clicks > 0" class="text-[11px] font-bold text-[#C41E3A]">{{ fmtN(a.clicks) }} cl.</span>
                    <span v-if="a.likes  > 0" class="text-[11px] font-bold text-[#C41E3A]/60">{{ fmtN(a.likes) }} ♥</span>
                  </div>
                </a>
              </div>
            </div>

            <!-- Per categoria -->
            <div v-if="modal.data.by_category?.length">
              <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">Per categoria</p>
              <div class="space-y-2">
                <div v-for="row in modal.data.by_category" :key="row.category">
                  <div class="flex justify-between text-xs mb-1">
                    <span class="capitalize text-gray-600">{{ row.category }}</span>
                    <span class="font-bold text-[#1A1A1A]">{{ fmtN(row.count) }}</span>
                  </div>
                  <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-[#C41E3A]/60 rounded-full transition-all duration-700"
                         :style="{ width: (row.count / modal.data.by_category[0].count * 100) + '%' }"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Contenuto: coverage articolo -->
          <div v-else-if="modal.type === 'coverage' && modal.data" class="overflow-y-auto p-5">
            <p class="text-sm text-gray-500 mb-4">
              <span class="font-bold text-[#C41E3A]">{{ modal.data.articles?.length }}</span>
              {{ modal.data.articles?.length === 1 ? 'testata copre' : 'testate coprono' }} questa notizia
            </p>
            <div class="space-y-2">
              <a v-for="a in modal.data.articles" :key="a.id"
                 :href="a.url" target="_blank" rel="noopener noreferrer"
                 class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 hover:bg-[#fff5f5] border border-transparent hover:border-[#f5c0c8] transition-all group">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="text-xs font-bold text-[#C41E3A]">{{ a.source_name }}</span>
                    <span v-if="a.is_main"
                          class="text-[9px] bg-[#C41E3A]/10 text-[#C41E3A] rounded px-1.5 py-0.5 font-bold uppercase tracking-wider">
                      principale
                    </span>
                  </div>
                  <p class="text-[13px] text-[#1A1A1A] leading-snug group-hover:text-[#C41E3A] transition-colors line-clamp-3">{{ a.title }}</p>
                  <p class="text-[10px] text-gray-400 mt-1">{{ fmtDate(a.published_at) }}</p>
                </div>
                <svg class="flex-shrink-0 w-4 h-4 text-gray-300 group-hover:text-[#C41E3A] transition-colors mt-1"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, defineComponent, h } from 'vue';
import axios from 'axios';

// ── SectionTitle ─────────────────────────────────────────────────────────────

const SectionTitle = defineComponent({
  setup(_, { slots }) {
    return () => h('div', { class: 'flex items-center gap-4' }, [
      h('h2', { class: 'font-display text-xl font-bold text-[#1A1A1A] whitespace-nowrap' }, slots.default?.()),
      h('div', { class: 'flex-1 h-px bg-gray-200' }),
    ]);
  },
});

// ── StackedRank — rolodex verticale ──────────────────────────────────────────

const StackedRank = defineComponent({
  props: {
    title:       String,
    rows:        Array,
    nameKey:     String,
    valueKey:    String,
    suffix:      String,
    empty:       { type: String, default: 'Nessun dato' },
    longName:    { type: Boolean, default: false },
    clickable:   { type: Boolean, default: false },
  },
  emits: ['itemClick'],
  setup(props, { emit }) {
    const activeIdx = ref(0);

    return () => {
      const rows   = props.rows ?? [];
      const n      = rows.length;
      const SLOT_H = props.longName ? 80 : 68; // px per slot
      const VISIBLE = 5;                        // quante card nel "finestrino"
      const winH   = SLOT_H * VISIBLE;

      if (!n) return h('div', {}, [
        h('p', { style: { fontSize:'10px', fontWeight:'700', textTransform:'uppercase', letterSpacing:'0.1em', color:'#9ca3af', marginBottom:'12px' } }, props.title),
        h('p', { style: { fontSize:'12px', color:'#9ca3af', fontStyle:'italic', textAlign:'center', paddingTop:'20px' } }, props.empty),
      ]);

      // Il tamburo scorre in modo che la card attiva sia centrata nel finestrino
      const centerOffset = Math.floor(VISIBLE / 2); // 2
      const translateY   = -((activeIdx.value - centerOffset) * SLOT_H);

      const cards = rows.map((row, i) => {
        const dist     = Math.abs(i - activeIdx.value);
        const isActive = i === activeIdx.value;
        // Larghezza: 100% per la card attiva, si restringe per le altre
        const width    = Math.max(76, 100 - dist * 8);
        const left     = ((100 - width) / 2).toFixed(1);
        const opacity  = Math.max(0.35, 1 - dist * 0.18);
        const shadow   = isActive
          ? '0 8px 28px rgba(196,30,58,0.18), 0 2px 8px rgba(0,0,0,0.07)'
          : '0 2px 8px rgba(0,0,0,0.06)';

        return h('div', {
          key: i,
          style: { height: SLOT_H + 'px', display: 'flex', alignItems: 'center', cursor: props.clickable ? 'pointer' : 'default' },
          onMouseenter: () => { activeIdx.value = i; },
          onClick: props.clickable ? () => emit('itemClick', row) : undefined,
        }, [
          h('div', {
            style: {
              width:        width + '%',
              marginLeft:   left + '%',
              padding:      '10px 13px',
              fontSize:     '13px',
              borderRadius: isActive ? '7px' : '0 0 7px 7px',
              background:   isActive ? 'linear-gradient(135deg,#fff 60%,#fff5f5 100%)' : 'white',
              boxShadow:    shadow,
              borderLeft:   isActive ? '3px solid #C41E3A' : '3px solid transparent',
              opacity:      String(opacity),
              transition:   'width 0.35s ease, margin-left 0.35s ease, opacity 0.35s ease, box-shadow 0.25s, border-radius 0.2s',
              boxSizing:    'border-box',
              position:     'relative',
            },
          }, [
            isActive ? h('span', { style: { position:'absolute', top:'5px', right:'7px', fontSize:'9px', fontWeight:'800', color:'#C41E3A', opacity:'0.4', textTransform:'uppercase', letterSpacing:'0.07em' } }, '№ ' + (i + 1)) : null,
            h('div', { style: { fontWeight: isActive ? '700' : '600', color:'#1A1A1A', lineHeight:'1.35', paddingRight: isActive ? '24px' : '0', wordBreak:'break-word' } }, row[props.nameKey]),
            h('div', { style: { fontWeight:'700', color:'#C41E3A', marginTop:'3px', fontSize:'11px' } },
              `${Number(row[props.valueKey]).toLocaleString('it-IT')} ${props.suffix ?? ''}`),
          ]),
        ]);
      });

      return h('div', {}, [
        h('p', { style: { fontSize:'10px', fontWeight:'700', textTransform:'uppercase', letterSpacing:'0.1em', color:'#9ca3af', marginBottom:'12px' } }, props.title),
        // Finestrino con gradient mask per effetto cilindro
        h('div', {
          style: {
            height:              winH + 'px',
            overflow:            'hidden',
            position:            'relative',
            WebkitMaskImage:     'linear-gradient(to bottom, transparent 0%, black 18%, black 82%, transparent 100%)',
            maskImage:           'linear-gradient(to bottom, transparent 0%, black 18%, black 82%, transparent 100%)',
          },
        }, [
          // Tamburo scorrevole
          h('div', {
            style: {
              transform:  `translateY(${translateY}px)`,
              transition: 'transform 0.45s cubic-bezier(0.25,0.46,0.45,0.94)',
            },
          }, cards),
        ]),
      ]);
    };
  },
});

// ── Modal ─────────────────────────────────────────────────────────────────────

const modal = ref({ open: false, type: null, data: null, loading: false, title: '' });

function closeModal() { modal.value.open = false; }

async function openSourceModal(row) {
  modal.value = { open: true, type: 'source', data: null, loading: true, title: row.source_name };
  try {
    const res = await axios.get('/api/analytics/source', { params: { domain: row.source_domain } });
    modal.value.data = res.data;
  } catch { modal.value.data = null; }
  finally { modal.value.loading = false; }
}

async function openCoverageModal(row) {
  modal.value = { open: true, type: 'coverage', data: null, loading: true, title: row.title };
  try {
    const res = await axios.get(`/api/articles/${row.id}/coverage`);
    modal.value.data = res.data;
  } catch { modal.value.data = null; }
  finally { modal.value.loading = false; }
}

const sourceModalCards = computed(() => {
  if (!modal.value.data?.totals) return [];
  const t = modal.value.data.totals;
  return [
    { label: 'Articoli', value: fmtN(t.articles), accent: false },
    { label: 'Click',    value: fmtN(t.clicks),   accent: true  },
    { label: 'Like',     value: fmtN(t.likes),    accent: true  },
    { label: 'Share',    value: fmtN(t.shares),   accent: true  },
  ];
});

function fmtDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' });
}

// ── State ─────────────────────────────────────────────────────────────────────

const data           = ref(null);
const loading        = ref(true);
const error          = ref(false);
const activeCategory = ref(null);

const categories = [
  { value: null,         label: 'Tutte' },
  { value: 'politica',   label: 'Politica' },
  { value: 'economia',   label: 'Economia' },
  { value: 'esteri',     label: 'Esteri' },
  { value: 'tecnologia', label: 'Tech' },
  { value: 'sport',      label: 'Sport' },
  { value: 'cultura',    label: 'Cultura' },
  { value: 'generale',   label: 'Generale' },
  { value: 'scienza',    label: 'Scienza' },
  { value: 'salute',     label: 'Salute' },
  { value: 'ambiente',   label: 'Ambiente' },
  { value: 'istruzione', label: 'Istruzione' },
  { value: 'cibo',       label: 'Cibo' },
  { value: 'viaggi',     label: 'Viaggi' },
];

// ── Helpers ───────────────────────────────────────────────────────────────────

function top(arr, n)  { return (arr ?? []).slice(0, n); }
function fmtN(n)      { return Number(n).toLocaleString('it-IT'); }

const generatedAt = computed(() => {
  if (!data.value?.generated_at) return '';
  return new Date(data.value.generated_at).toLocaleString('it-IT');
});

const totalsCards = computed(() => {
  if (!data.value) return [];
  const t = data.value.totals;
  return [
    { label: 'Articoli', value: fmtN(t.articles), accent: false },
    { label: 'Topic',    value: fmtN(t.topics),   accent: false },
    { label: 'Testate',  value: fmtN(t.sources),  accent: false },
    { label: 'Click',    value: fmtN(t.clicks),   accent: true  },
    { label: 'Like',     value: fmtN(t.likes),    accent: true  },
    { label: 'Share',    value: fmtN(t.shares),   accent: true  },
  ];
});

// ── Orientamento ──────────────────────────────────────────────────────────────

const leanMeta = {
  'left':          { label: 'Sinistra',        short: 'Sx',    color: '#1D4ED8' },
  'center-left':   { label: 'Centro-sinistra', short: 'C.Sx',  color: '#60A5FA' },
  'center':        { label: 'Centro',          short: 'Cen.',  color: '#6B7280' },
  'center-right':  { label: 'Centro-destra',   short: 'C.Dx',  color: '#FB923C' },
  'right':         { label: 'Destra',          short: 'Dx',    color: '#DC2626' },
  'international': { label: 'Internazionale',  short: "Int'l", color: '#D97706' },
  'altro':         { label: 'Neutri',          short: 'Neu.',  color: '#7C3AED' },
};

function leanColor(lean) { return leanMeta[lean]?.color ?? '#9CA3AF'; }
function leanLabel(lean) { return leanMeta[lean]?.label ?? lean; }
function leanShort(lean) { return leanMeta[lean]?.short ?? lean; }
function leanPct(val, total) { return total > 0 ? Math.round(Number(val) / total * 100) : 0; }
function leanTotal(rows, key) { return rows.reduce((s, r) => s + Number(r[key]), 0); }

// ── Load ──────────────────────────────────────────────────────────────────────

async function load() {
  loading.value = true;
  error.value   = false;
  try {
    const params = activeCategory.value ? { category: activeCategory.value } : {};
    const res    = await axios.get('/api/analytics', { params });
    data.value   = res.data;
  } catch {
    error.value = true;
  } finally {
    loading.value = false;
  }
}

function selectCategory(val) {
  activeCategory.value = val;
  load();
}

onMounted(load);
</script>

<style>
.modal-enter-active,
.modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-active .relative,
.modal-leave-active .relative { transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1), opacity 0.2s ease; }
.modal-enter-from,
.modal-leave-to { opacity: 0; }
.modal-enter-from .relative { transform: translateY(40px); opacity: 0; }
.modal-leave-to  .relative { transform: translateY(20px); opacity: 0; }
</style>
