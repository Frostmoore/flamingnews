<template>
  <div class="min-h-screen bg-[#F8F6F1]">

    <!-- Header: striscia rossa + logo + auth -->
    <header class="sticky top-0 z-30 bg-white shadow-sm">
      <!-- Striscia rossa superiore -->
      <div class="bg-[#C41E3A] h-1 w-full"></div>

      <!-- Logo + auth / barra ricerca -->
      <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between border-b border-gray-100">
        <!-- Modalità normale -->
        <template v-if="!searchActive">
          <a href="/" class="font-display text-2xl font-bold text-[#1A1A1A] tracking-tight flex-shrink-0">
            Flaming<span class="text-[#C41E3A]">News</span>
          </a>
          <div class="flex items-center gap-3">

            <!-- ── Desktop ─────────────────────────────────────── -->
            <a href="/analytics"
               class="hidden sm:inline text-xs font-semibold text-gray-400 hover:text-[#C41E3A] transition-colors uppercase tracking-wide"
               title="Classifiche">Classifiche</a>

            <button v-if="isAuthenticated"
              @click="selectCategory('__myfeeds__')"
              class="hidden sm:inline text-xs font-semibold uppercase tracking-wide transition-colors"
              :class="activeCategory === '__myfeeds__' ? 'text-[#C41E3A]' : 'text-gray-400 hover:text-[#C41E3A]'"
              title="I miei feed RSS">Miei feed</button>

            <button @click="openSearch"
              class="hidden sm:flex text-gray-400 hover:text-[#1A1A1A] transition-colors" title="Cerca">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
              </svg>
            </button>

            <template v-if="isAuthenticated">
              <a href="/profile" class="hidden sm:flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-[#C41E3A] transition-colors" title="Il mio profilo">
                <div class="w-6 h-6 rounded-full bg-[#C41E3A] flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">{{ userName[0]?.toUpperCase() }}</div>
                <span>{{ userName }}</span>
              </a>
              <button @click="toggleAuth"
                class="hidden sm:inline px-4 py-1.5 text-xs font-bold tracking-wide border border-gray-300 hover:border-[#C41E3A] hover:text-[#C41E3A] transition-colors uppercase">Esci</button>
            </template>
            <template v-else>
              <a href="/login" class="hidden sm:inline px-4 py-1.5 text-xs font-bold tracking-wide border border-gray-300 hover:border-[#C41E3A] hover:text-[#C41E3A] transition-colors uppercase">Accedi</a>
            </template>

            <!-- ── Mobile: menu a comparsa ──────────────────────── -->
            <div v-if="menuOpen" class="fixed inset-0 z-30 sm:hidden" @click="menuOpen = false"></div>
            <div class="sm:hidden relative z-40">
              <button @click="menuOpen = !menuOpen" class="text-gray-500 hover:text-[#1A1A1A] transition-colors p-1" aria-label="Menu">
                <svg v-if="!menuOpen" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>

              <div v-if="menuOpen" class="absolute right-0 top-full mt-2 bg-white border border-gray-200 shadow-xl w-52 py-1">

                <!-- Cerca -->
                <button @click="menuOpen = false; openSearch()"
                  class="w-full flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                  <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                  </svg>
                  Cerca
                </button>

                <div class="border-t border-gray-100"></div>

                <!-- Classifiche -->
                <a href="/analytics" @click="menuOpen = false"
                  class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                  <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                  Classifiche
                </a>

                <!-- Miei feed (solo auth) -->
                <button v-if="isAuthenticated"
                  @click="menuOpen = false; selectCategory('__myfeeds__')"
                  class="w-full flex items-center gap-3 px-4 py-3 text-sm hover:bg-gray-50 transition-colors text-left"
                  :class="activeCategory === '__myfeeds__' ? 'text-[#C41E3A] font-semibold' : 'text-gray-700'">
                  <svg class="w-4 h-4 flex-shrink-0" :class="activeCategory === '__myfeeds__' ? 'text-[#C41E3A]' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                  </svg>
                  Miei feed
                </button>

                <div class="border-t border-gray-100"></div>

                <!-- Profilo / Accedi -->
                <template v-if="isAuthenticated">
                  <a href="/profile" @click="menuOpen = false"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <div class="w-5 h-5 rounded-full bg-[#C41E3A] flex items-center justify-center text-white text-[9px] font-bold flex-shrink-0">{{ userName[0]?.toUpperCase() }}</div>
                    {{ userName }}
                  </a>
                  <button @click="menuOpen = false; toggleAuth()"
                    class="w-full flex items-center gap-3 px-4 py-3 text-sm text-[#C41E3A] font-semibold hover:bg-red-50 transition-colors text-left">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Esci
                  </button>
                </template>
                <template v-else>
                  <a href="/login" @click="menuOpen = false"
                    class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-[#C41E3A] hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Accedi
                  </a>
                </template>
              </div>
            </div>

          </div>
        </template>

        <!-- Modalità ricerca -->
        <template v-else>
          <button @click="closeSearch" class="text-gray-400 hover:text-[#1A1A1A] transition-colors flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 5l-7 7 7 7"/>
            </svg>
          </button>
          <input
            ref="searchInput"
            v-model="searchQuery"
            @input="onSearchInput"
            @keydown.esc="closeSearch"
            type="search"
            placeholder="Cerca articoli…"
            class="flex-1 mx-4 text-sm outline-none bg-transparent placeholder-gray-400"
          />
          <button v-if="searchQuery" @click="clearSearch" class="text-gray-400 hover:text-[#1A1A1A] transition-colors flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </template>
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

        <!-- ── Masthead (Temi tab) ── -->
        <div v-if="activeCategory === null" class="text-center py-8 mb-2 border-b border-gray-300">
          <div class="flex items-center justify-center gap-1 mb-3">
            <div class="h-px flex-1 bg-[#C41E3A] max-w-[80px]"></div>
            <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#C41E3A] px-3">dal 2025</span>
            <div class="h-px flex-1 bg-[#C41E3A] max-w-[80px]"></div>
          </div>
          <h1 class="font-display text-5xl md:text-7xl font-bold tracking-tight leading-none mb-3">
            Flaming<span class="text-[#C41E3A]">News</span>
          </h1>
          <p class="text-sm md:text-base text-gray-500 tracking-widest uppercase font-medium">
            Tutte le voci. Tutte le visioni.
          </p>
        </div>

        <!-- ── Hero desktop: sidebar | slider | coverage (solo Temi, ≥7 art.) ── -->
        <div v-if="showHero" class="hidden md:grid grid-cols-[0.9fr_2fr_1.1fr] gap-0 mb-8 h-[380px]">

          <!-- Sidebar sinistra: 1 card full-bleed + coverage scrollabile -->
          <div class="flex flex-col h-full overflow-hidden mr-4">
            <!-- Articolo -->
            <div
              v-if="sidebarLeft[0]"
              class="relative flex-shrink-0 h-40 overflow-hidden cursor-pointer group/card"
              @click="openArticle(sidebarLeft[0])"
            >
              <img
                v-if="sidebarLeft[0].url_to_image"
                :src="sidebarLeft[0].url_to_image"
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover/card:scale-105"
                alt=""
              />
              <div v-else class="absolute inset-0 bg-gray-300"></div>
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
              <div class="absolute bottom-0 left-0 right-0 p-3">
                <span v-if="sidebarLeft[0].category" class="text-[9px] uppercase font-bold text-[#C41E3A] bg-white/90 px-1.5 py-0.5 inline-block mb-1">{{ sidebarLeft[0].category }}</span>
                <p class="text-white text-xs font-semibold leading-snug line-clamp-3">{{ sidebarLeft[0].title }}</p>
              </div>
            </div>
            <!-- Coverage dell'articolo sidebar -->
            <div class="flex-1 overflow-hidden bg-white flex flex-col">
              <div class="px-3 py-2 border-b border-gray-100 flex-shrink-0">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Come ne parlano</p>
              </div>
              <div class="flex-1 overflow-y-auto px-3 py-1 space-y-0.5">
                <template v-if="sidebarLeft[0]?.coverage?.length">
                  <template v-for="lean in ['left','center-left','center','center-right','right','international','altro']" :key="lean">
                    <template v-if="coverageByLean(sidebarLeft[0], lean).length">
                      <div class="pt-2 pb-1">
                        <span class="inline-block text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-full text-white" :style="{ background: leanHex[lean] }">
                          {{ leanLabel[lean] }}
                        </span>
                      </div>
                      <a
                        v-for="src in coverageByLean(sidebarLeft[0], lean)" :key="src.id"
                        :href="src.url" target="_blank" rel="noopener"
                        @click.stop
                        class="block py-1.5 border-l-2 pl-2 hover:bg-gray-50 transition-colors"
                        :style="{ borderColor: leanHex[lean] }"
                      >
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide block">{{ src.source_name }}</span>
                        <span class="text-xs text-gray-700 leading-snug line-clamp-2 block">{{ src.title }}</span>
                      </a>
                    </template>
                  </template>
                </template>
                <div v-else class="py-4 text-xs text-gray-400 text-center">
                  Solo questa testata ha pubblicato la notizia
                </div>
              </div>
            </div>
          </div>

          <!-- Slider centrale -->
          <div
            class="relative overflow-hidden bg-gray-900 cursor-pointer group h-full"
            @mouseenter="stopSliderTimer"
            @mouseleave="startSliderTimer"
            @click="openArticle(sliderArticles[sliderIndex])"
          >
            <transition name="slider-fade">
              <img
                v-if="sliderArticles[sliderIndex]?.url_to_image"
                :key="sliderIndex"
                :src="sliderArticles[sliderIndex].url_to_image"
                class="absolute inset-0 w-full h-full object-cover"
                alt=""
              />
            </transition>
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>

            <!-- Testo overlay -->
            <div class="absolute bottom-0 left-0 right-0 p-5 pb-10">
              <span v-if="sliderArticles[sliderIndex]?.source_name"
                class="inline-block text-[10px] uppercase font-bold bg-[#C41E3A] text-white px-2 py-0.5 mb-2">
                {{ sliderArticles[sliderIndex].source_name }}
              </span>
              <h2 class="text-white text-xl font-bold leading-snug line-clamp-3">
                {{ sliderArticles[sliderIndex]?.title }}
              </h2>
              <p v-if="sliderArticles[sliderIndex]?.description"
                class="text-white/65 text-xs mt-1.5 line-clamp-2">
                {{ sliderArticles[sliderIndex].description }}
              </p>
            </div>

            <!-- Prev / Next -->
            <button
              @click.stop="prevSlide()"
              class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100"
              aria-label="Precedente"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <button
              @click.stop="nextSlide()"
              class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100"
              aria-label="Successivo"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </button>

            <!-- Dots -->
            <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5">
              <button
                v-for="(_, i) in sliderArticles" :key="i"
                @click.stop="goToSlide(i)"
                class="w-2 h-2 rounded-full transition-all"
                :class="i === sliderIndex ? 'bg-white' : 'bg-white/45 hover:bg-white/70'"
                :aria-label="`Slide ${i + 1}`"
              ></button>
            </div>
          </div>

          <!-- Coverage panel destra -->
          <div class="bg-white h-full flex flex-col overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex-shrink-0">
              <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Come ne parlano</p>
            </div>
            <div class="flex-1 overflow-y-auto px-3 py-2 space-y-0.5">
              <template v-if="sliderArticles[sliderIndex]?.coverage?.length">
                <template v-for="lean in ['left','center-left','center','center-right','right','international','altro']" :key="lean">
                  <template v-if="coverageByLean(sliderArticles[sliderIndex], lean).length">
                    <div class="pt-2 pb-1">
                      <span class="inline-block text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-full text-white" :style="{ background: leanHex[lean] }">
                        {{ leanLabel[lean] }}
                      </span>
                    </div>
                    <a
                      v-for="src in coverageByLean(sliderArticles[sliderIndex], lean)" :key="src.id"
                      :href="src.url" target="_blank" rel="noopener"
                      @click.stop
                      class="block py-2 border-l-2 pl-2 hover:bg-gray-50 transition-colors"
                      :style="{ borderColor: leanHex[lean] }"
                    >
                      <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide block">{{ src.source_name }}</span>
                      <span class="text-xs text-gray-700 leading-snug line-clamp-2 block">{{ src.title }}</span>
                    </a>
                  </template>
                </template>
              </template>
              <div v-else class="py-4 text-xs text-gray-400 text-center">
                Solo questa testata ha pubblicato la notizia
              </div>
            </div>
          </div>
        </div>

        <!-- Disclaimer -->
        <div v-if="activeCategory === null" class="text-center text-[11px] text-gray-400 leading-relaxed mb-6 px-2">
          Le notizie sono tratte da feed RSS pubblici. La classificazione politica delle testate è stata definita con il supporto di professionisti del settore, ma non può essere considerata assoluta o priva di margine d'errore.
        </div>

        <!-- Primo articolo in evidenza (mobile sempre; desktop solo senza hero) -->
        <div v-if="articles.length > 0" :class="showHero ? 'mb-6 md:hidden' : 'mb-6'">
          <ArticleCard
            :article="articles[0]"
            :featured="true"
            @click="openArticle"
            @like="toggleLike"
            @share="shareArticle"
          />
        </div>

        <!-- Griglia desktop hero-filtered (visibile solo su desktop quando hero attivo) -->
        <div v-if="showHero" class="hidden md:block">
          <TransitionGroup tag="div" class="articles-grid" name="feed-in">
            <div v-for="(item, index) in feedItemsDesktop" :key="'d-' + item.type + '-' + index"
                 :class="item.type === 'ad' ? 'col-span-full' : 'contents'">
              <AdUnit v-if="item.type === 'ad'" :publisher-id="adsensePublisher" :slot="adsenseFeedSlot" />
              <ArticleCard
                v-else
                :article="item.article"
                :compact="true"
                @click="openArticle"
                @like="toggleLike"
                @share="shareArticle"
              />
            </div>
          </TransitionGroup>
        </div>

        <!-- Griglia normale (mobile sempre; desktop solo senza hero) -->
        <div :class="{ 'md:hidden': showHero }">
          <TransitionGroup tag="div" class="articles-grid" name="feed-in">
            <div v-for="(item, index) in feedItems" :key="'m-' + item.type + '-' + index"
                 :class="item.type === 'ad' ? 'col-span-full' : 'contents'">
              <AdUnit v-if="item.type === 'ad'" :publisher-id="adsensePublisher" :slot="adsenseFeedSlot" />
              <ArticleCard
                v-else
                :article="item.article"
                :compact="true"
                @click="openArticle"
                @like="toggleLike"
                @share="shareArticle"
              />
            </div>
          </TransitionGroup>
        </div>

        <!-- Sentinel lazy load -->
        <div ref="sentinel" class="h-10"></div>

        <!-- Spinner caricamento ulteriori articoli -->
        <div v-if="loadingMore" class="flex justify-center py-6">
          <svg class="animate-spin w-6 h-6 text-[#C41E3A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
          </svg>
        </div>
      </template>
    </div>

    <!-- Torna all'inizio — appare dopo il primo lazy-load -->
    <Transition name="scroll-top">
      <button
        v-if="showScrollTop"
        @click="scrollToTop"
        class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-[#C41E3A] text-white shadow-lg flex items-center justify-center hover:bg-red-800 transition-colors"
        title="Torna all'inizio"
        aria-label="Torna all'inizio"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
        </svg>
      </button>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import axios from 'axios';
import { useArticles } from '../composables/useArticles';
import { useAuth } from '../composables/useAuth';
import ArticleCard from './ArticleCard.vue';
import AdUnit from './AdUnit.vue';

const props = defineProps({
  adsensePublisher:  { type: String, default: '' },
  adsenseFeedSlot:   { type: String, default: '' },
  adsenseFrequency:  { type: String, default: '6' },
});

const { articles, meta, loading, loadingMore, hasMore, error, fetchArticles, toggleLike, shareArticle } = useArticles();
const { user, isAuthenticated, logout } = useAuth();
const userName = computed(() => user.value?.name ?? '');

const activeCategory  = ref(null); // null=Temi, '__all__'=Tutte, '__myfeeds__'=Miei feed, string=categoria
const sentinel        = ref(null);
const showScrollTop   = computed(() => (meta.value?.current_page ?? 1) > 1);
const menuOpen        = ref(false);
let observer = null;

// ── Ricerca ───────────────────────────────────────────────────────────────
const searchActive = ref(false);
const searchQuery  = ref('');
const searchInput  = ref(null);
let   searchTimer  = null;

async function openSearch() {
  searchActive.value = true;
  await nextTick();
  searchInput.value?.focus();
}

function closeSearch() {
  searchActive.value = false;
  searchQuery.value  = '';
  clearTimeout(searchTimer);
  load(1); // ricarica con il tab/categoria attivi
}

function clearSearch() {
  searchQuery.value = '';
  clearTimeout(searchTimer);
  load(1);
  searchInput.value?.focus();
}

function onSearchInput() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => load(1), 350);
}

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

// ── Hero slider (Temi desktop) ───────────────────────────────────────────
const sliderIndex = ref(0);
let sliderTimer = null;

const showHero = computed(() =>
  activeCategory.value === null && !loading.value && articles.value.length >= 7
);

const sliderArticles = computed(() => {
  const withImg = articles.value.filter(a => a.url_to_image).slice(0, 3);
  if (withImg.length >= 3) return withImg;
  const used = new Set(withImg.map(a => a.id));
  const fill = articles.value.filter(a => !used.has(a.id)).slice(0, 3 - withImg.length);
  return [...withImg, ...fill];
});

const sidebarArticles = computed(() => {
  const sliderIds  = new Set(sliderArticles.value.map(a => a.id));
  const sliderCats = new Set(sliderArticles.value.map(a => a.category).filter(Boolean));
  const remaining  = articles.value.filter(a => !sliderIds.has(a.id));
  const selected   = [];
  // prima passa: con immagine e categoria diversa
  for (const a of remaining) {
    if (selected.length === 1) break;
    if (a.url_to_image && !sliderCats.has(a.category)) selected.push(a);
  }
  // seconda passa: con immagine qualsiasi
  for (const a of remaining) {
    if (selected.length === 1) break;
    if (a.url_to_image && !selected.some(s => s.id === a.id)) selected.push(a);
  }
  // terza passa: fallback senza immagine
  for (const a of remaining) {
    if (selected.length === 1) break;
    if (!selected.some(s => s.id === a.id)) selected.push(a);
  }
  return selected;
});

const sidebarLeft = computed(() => sidebarArticles.value.slice(0, 2));

const heroUsedIds = computed(() => {
  if (!showHero.value) return new Set();
  return new Set([
    ...sliderArticles.value.map(a => a.id),
    ...sidebarArticles.value.map(a => a.id),
  ]);
});

const feedItemsDesktop = computed(() => {
  const freq = parseInt(props.adsenseFrequency) || 6;
  const filtered = articles.value.filter(a => !heroUsedIds.value.has(a.id));
  const items = [];
  filtered.forEach((article, i) => {
    if (i > 0 && i % freq === 0) items.push({ type: 'ad' });
    items.push({ type: 'article', article });
  });
  return items;
});

const leanHex = {
  'left':          '#dc2626',
  'center-left':   '#f97316',
  'center':        '#eab308',
  'center-right':  '#3b82f6',
  'right':         '#1d4ed8',
  'international': '#166534',
  'altro':         '#6b7280',
};
const leanLabel = {
  'left':          'Sinistra',
  'center-left':   'Centro-sin.',
  'center':        'Centro',
  'center-right':  'Centro-des.',
  'right':         'Destra',
  'international': "Int'l",
  'altro':         'Neutri',
};
function coverageByLean(article, lean) {
  const seen = new Set([article.source_domain]);
  return (article.coverage ?? []).filter(src => {
    if (src.lean !== lean) return false;
    if (seen.has(src.source_domain)) return false;
    seen.add(src.source_domain);
    return true;
  });
}

function nextSlide() {
  sliderIndex.value = (sliderIndex.value + 1) % sliderArticles.value.length;
}
function prevSlide() {
  sliderIndex.value = (sliderIndex.value - 1 + sliderArticles.value.length) % sliderArticles.value.length;
}
function goToSlide(i) {
  sliderIndex.value = i;
  stopSliderTimer();
  if (showHero.value) startSliderTimer();
}
function startSliderTimer() {
  clearInterval(sliderTimer);
  sliderTimer = setInterval(() => nextSlide(), 5000);
}
function stopSliderTimer() {
  clearInterval(sliderTimer);
  sliderTimer = null;
}

watch(showHero, (val) => {
  if (val) { sliderIndex.value = 0; startSliderTimer(); }
  else stopSliderTimer();
});

const categories = [
  { value: null,          label: 'Temi' },
  { value: '__all__',     label: 'Tutte' },
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

async function loadMyFeeds(page = 1) {
  const appending = page > 1;
  if (appending) loadingMore.value = true;
  else loading.value = true;
  error.value = null;
  try {
    const token = localStorage.getItem('fn_token');
    const res = await axios.get('/api/my-feeds/articles', {
      params: { page, per_page: 20 },
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    });
    articles.value = appending ? [...articles.value, ...res.data.data] : res.data.data;
    meta.value = res.data.meta;
  } catch (e) {
    error.value = 'Errore nel caricamento dei tuoi feed.';
  } finally {
    loading.value = false;
    loadingMore.value = false;
  }
}

async function load(page = 1) {
  if (activeCategory.value === '__myfeeds__') {
    await loadMyFeeds(page);
    if (page === 1) await nextTick().then(setupObserver);
    return;
  }
  const isAll = activeCategory.value === '__all__';
  const category = (isAll || activeCategory.value === null) ? null : activeCategory.value;
  const tab = isAll ? 'tutte' : null;
  const isTemi = activeCategory.value === null;
  const perPage = isTemi ? 12 : 20;
  await fetchArticles({ category, tab, page, perPage, q: searchQuery.value });
  if (page === 1) await nextTick().then(setupObserver);
}

async function loadMore() {
  if (loadingMore.value || loading.value) return;
  if (hasMore()) await load(meta.value.current_page + 1);
}

function setupObserver() {
  if (observer) observer.disconnect();
  observer = new IntersectionObserver(
    (entries) => { if (entries[0].isIntersecting) loadMore(); },
    { rootMargin: '400px' }
  );
  if (sentinel.value) observer.observe(sentinel.value);
}

function selectCategory(value) {
  activeCategory.value = value;
  menuOpen.value = false;
  load(1);
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function openArticle(article) {
  axios.post(`/api/articles/${article.id}/click`).catch(() => {});
  window.open(article.url, '_blank', 'noopener,noreferrer');
}

function toggleAuth() {
  if (isAuthenticated.value) {
    logout().then(() => window.location.reload());
  } else {
    window.location.href = '/login';
  }
}

onMounted(async () => {
  await load();
  await nextTick();
  setupObserver();
});

onBeforeUnmount(() => { if (observer) observer.disconnect(); stopSliderTimer(); });
</script>

<style scoped>
.scroll-top-enter-active,
.scroll-top-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.scroll-top-enter-from,
.scroll-top-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.slider-fade-enter-active,
.slider-fade-leave-active {
  transition: opacity 0.5s ease;
  position: absolute;
  inset: 0;
}
.slider-fade-enter-from,
.slider-fade-leave-to {
  opacity: 0;
}

.feed-in-enter-active {
  transition: opacity 0.35s ease, transform 0.35s ease;
}
.feed-in-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
</style>
