@extends('admin.layout')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">

  <div>
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-sm text-gray-500 mt-1">Panoramica generale del sistema.</p>
  </div>

  <!-- Stats grid -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach ([
      ['label' => 'Utenti',          'value' => $stats['users'],          'sub' => $stats['admins'].' admin'],
      ['label' => 'Testate attive',  'value' => $stats['sources_active'], 'sub' => $stats['sources'].' totali'],
      ['label' => 'Articoli',        'value' => number_format($stats['articles']), 'sub' => $stats['topics'].' temi'],
      ['label' => 'Feed RSS attivi', 'value' => $stats['feeds'],          'sub' => $stats['user_feeds'].' feed utenti'],
    ] as $stat)
    <div class="bg-white rounded-sm border border-gray-200 p-5">
      <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">{{ $stat['label'] }}</p>
      <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stat['value'] }}</p>
      <p class="text-xs text-gray-400 mt-0.5">{{ $stat['sub'] }}</p>
    </div>
    @endforeach
  </div>

  <!-- Azioni rapide -->
  <div class="bg-white border border-gray-200 rounded-sm p-5" x-data="{ fetching: false, msg: '' }">
    <h2 class="text-sm font-bold uppercase tracking-widest text-gray-500 mb-4">Azioni rapide</h2>
    <div class="flex flex-wrap gap-3">

      <button
        @click="
          fetching = true; msg = '';
          fetch('{{ route('admin.fetch') }}', { method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'} })
            .then(r => r.json()).then(d => { msg = d.message ?? 'Avviato.'; })
            .catch(() => { msg = 'Errore.'; })
            .finally(() => fetching = false)
        "
        :disabled="fetching"
        class="inline-flex items-center gap-2 px-4 py-2 bg-[#C41E3A] text-white text-sm font-semibold hover:bg-red-800 disabled:opacity-50 transition-colors"
      >
        <svg class="w-4 h-4" :class="fetching ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <span x-text="fetching ? 'Avvio in corso…' : 'Avvia Fetch News'"></span>
      </button>

      <a href="{{ route('admin.users') }}"
         class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-700 hover:border-gray-500 transition-colors">
        Gestisci utenti
      </a>
      <a href="{{ route('admin.sources') }}"
         class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-700 hover:border-gray-500 transition-colors">
        Gestisci testate
      </a>
    </div>
    <p x-show="msg" x-text="msg" class="mt-3 text-sm text-green-600" x-cloak></p>
  </div>

</div>
@endsection
