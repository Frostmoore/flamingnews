@extends('layouts.app')
@section('title', 'Feed personale')
@section('requires_auth', true)

@section('content')
<div class="min-h-screen bg-[#F8F6F1]" x-data="myFeedPage()" x-init="init()">

  <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
    <div class="max-w-3xl mx-auto px-4 h-14 flex items-center gap-4">
      <a href="/profile" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <span class="font-bold text-[#1A1A1A]" x-text="feedName || 'Feed personale'"></span>
      <div class="ml-auto flex items-center gap-2">
        <button @click="refresh" :disabled="refreshing"
          class="text-gray-400 hover:text-[#1A1A1A] transition-colors" title="Aggiorna">
          <svg class="w-5 h-5" :class="{'animate-spin': refreshing}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M20 20v-5h-.581M5.635 15A9 9 0 1 0 4.582 9"/>
          </svg>
        </button>
      </div>
    </div>
  </header>

  <div class="max-w-3xl mx-auto px-4 py-6">

    <!-- Loading -->
    <div x-show="loading" class="space-y-4">
      <template x-for="n in 6">
        <div class="bg-white p-4 animate-pulse flex gap-4">
          <div class="w-20 h-16 bg-gray-200 flex-shrink-0"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-3 bg-gray-100 rounded w-1/2"></div>
          </div>
        </div>
      </template>
    </div>

    <!-- Errore -->
    <div x-show="error" x-cloak class="text-center py-16 text-red-600">
      <p class="font-semibold" x-text="error"></p>
    </div>

    <!-- Lista articoli -->
    <div x-show="!loading && !error" class="space-y-2">
      <template x-if="articles.length === 0">
        <div class="text-center py-16 text-gray-400 text-sm">Nessun articolo disponibile.</div>
      </template>
      <template x-for="article in articles" :key="article.id">
        <a :href="article.url" target="_blank" rel="noopener noreferrer"
          class="bg-white flex gap-4 p-4 hover:bg-gray-50 transition-colors group border border-transparent hover:border-gray-200">
          <img x-show="article.url_to_image" :src="article.url_to_image" :alt="article.title"
            class="w-20 h-16 object-cover flex-shrink-0 bg-gray-100" loading="lazy" />
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold text-[#1A1A1A] leading-snug group-hover:text-[#C41E3A] transition-colors line-clamp-2"
              x-text="article.title"></div>
            <div class="text-xs text-gray-400 mt-1 line-clamp-2" x-show="article.description" x-text="article.description"></div>
            <div class="text-xs text-gray-300 mt-1.5" x-text="formatDate(article.published_at)"></div>
          </div>
        </a>
      </template>

      <!-- Load more -->
      <div x-show="hasMore" class="text-center pt-4">
        <button @click="loadMore" :disabled="loadingMore"
          class="px-6 py-2 border border-gray-300 text-sm text-gray-600 hover:border-[#C41E3A] hover:text-[#C41E3A] transition-colors">
          <span x-show="!loadingMore">Carica altri</span>
          <span x-show="loadingMore">Caricamento...</span>
        </button>
      </div>
    </div>

  </div>
</div>

<script>
function myFeedPage() {
  const feedId = location.pathname.split('/').pop();
  return {
    feedId,
    feedName: '',
    articles: [],
    loading: true,
    error: '',
    refreshing: false,
    loadingMore: false,
    page: 1,
    hasMore: false,

    token() { return localStorage.getItem('fn_token'); },

    async init() {
      if (!this.token()) { window.location.href = '/login'; return; }
      await this.loadArticles(1);
    },

    async loadArticles(page) {
      if (page === 1) this.loading = true;
      else this.loadingMore = true;
      this.error = '';
      try {
        const res = await fetch(`/api/my-feeds/articles?page=${page}&per_page=30`, {
          headers: { 'Authorization': 'Bearer ' + this.token(), 'Accept': 'application/json' },
        });
        if (!res.ok) throw new Error('Errore nel caricamento.');
        const data = await res.json();
        // Filtra solo gli articoli di questo feed
        const items = data.data.filter(a => a.feed?.id == this.feedId || !this.feedId);
        if (page === 1) {
          this.articles = data.data;
          this.feedName = data.data[0]?.feed?.name ?? '';
        } else {
          this.articles.push(...data.data);
        }
        this.page = data.meta.current_page;
        this.hasMore = data.meta.current_page < data.meta.last_page;
      } catch (e) {
        this.error = e.message;
      } finally {
        this.loading = false;
        this.loadingMore = false;
      }
    },

    async loadMore() {
      await this.loadArticles(this.page + 1);
    },

    async refresh() {
      this.refreshing = true;
      try {
        await fetch(`/api/my-feeds/${this.feedId}/refresh`, {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + this.token(), 'Accept': 'application/json' },
        });
        await this.loadArticles(1);
      } finally {
        this.refreshing = false;
      }
    },

    formatDate(str) {
      if (!str) return '';
      return new Date(str).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
    },
  };
}
</script>
@endsection
