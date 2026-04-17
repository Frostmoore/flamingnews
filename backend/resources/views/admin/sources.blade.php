@extends('admin.layout')

@section('content')
@php
$leanColors = [
  'left'          => '#1D4ED8',
  'center-left'   => '#60A5FA',
  'center'        => '#6B7280',
  'center-right'  => '#FB923C',
  'right'         => '#DC2626',
  'international' => '#166534',
  'altro'         => '#7C3AED',
];
$leanLabels = [
  'left'          => 'Sinistra',
  'center-left'   => 'Centro-sinistra',
  'center'        => 'Centro',
  'center-right'  => 'Centro-destra',
  'right'         => 'Destra',
  'international' => 'Internazionale',
  'altro'         => 'Media neutri',
];
@endphp

{{-- Dati PHP → JS: script standard (nessun problema di escaping in attributi HTML) --}}
<script>
window._leanList   = @json($leans);
window._leanLabels = @json($leanLabels);
window._leanColors = @json($leanColors);
window._sources    = {};
@foreach($sources as $src)
window._sources[{{ $src->id }}] = {
  lean:      @json($src->political_lean),
  tier:      {{ (int)$src->tier }},
  active:    {{ $src->active ? 'true' : 'false' }},
  feedUrl:   @json($src->feed_url ?? ''),
  name:      @json($src->name),
  updateUrl: @json(route('admin.sources.update', $src)),
  deleteUrl: @json(route('admin.sources.delete', $src)),
};
@endforeach
</script>

<div class="max-w-7xl mx-auto space-y-6" x-data="sourcesAdmin()">

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Testate</h1>
      <p class="text-sm text-gray-500 mt-1">{{ $sources->total() }} testate.</p>
    </div>
    <button @click="showCreate = true"
            class="px-4 py-2 bg-[#C41E3A] text-white text-sm font-semibold hover:bg-red-800 transition-colors self-start">
      + Aggiungi testata
    </button>
  </div>

  <!-- Filtri -->
  <form method="GET" class="flex flex-wrap gap-2">
    <input type="search" name="q" value="{{ $q }}" placeholder="Cerca dominio o nome…"
           class="border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#C41E3A] w-56">
    <select name="lean" class="border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#C41E3A]">
      <option value="">Tutti gli orientamenti</option>
      @foreach ($leans as $l)
        <option value="{{ $l }}" @selected($lean === $l)>{{ $leanLabels[$l] }}</option>
      @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold hover:bg-gray-900">Filtra</button>
    @if ($q || $lean)
      <a href="{{ route('admin.sources') }}" class="px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-600 hover:bg-gray-50">Reset</a>
    @endif
  </form>

  <!-- Tabella -->
  <div class="bg-white border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-500">Testata</th>
            <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-500 hidden md:table-cell">Dominio</th>
            <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-500">Orientamento</th>
            <th class="text-center px-3 py-3 text-xs font-bold uppercase tracking-widest text-gray-500">Tier</th>
            <th class="text-center px-3 py-3 text-xs font-bold uppercase tracking-widest text-gray-500">Attiva</th>
            <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-500 hidden lg:table-cell">Feed RSS</th>
            <th class="text-right px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-500 hidden md:table-cell">Articoli</th>
            <th class="px-3 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach ($sources as $source)
          <tr class="hover:bg-gray-50 transition-colors" x-data="sourceRow({{ $source->id }})">

            <!-- Testata -->
            <td class="px-4 py-3">
              <template x-if="!editing">
                <span class="font-semibold text-gray-900" x-text="sourceName"></span>
              </template>
              <template x-if="editing">
                <input x-model="sourceName" class="border border-gray-300 px-2 py-1 text-sm w-40 focus:outline-none focus:border-[#C41E3A]">
              </template>
            </td>

            <!-- Dominio -->
            <td class="px-4 py-3 text-gray-500 font-mono text-xs hidden md:table-cell">{{ $source->domain }}</td>

            <!-- Orientamento: select sempre visibile, auto-salva -->
            <td class="px-4 py-3">
              <div class="relative inline-block">
                <select x-model="lean" @change="patch({ political_lean: lean })"
                  class="appearance-none text-[10px] font-bold uppercase pl-2 pr-5 py-0.5 rounded-full text-white cursor-pointer focus:outline-none"
                  :style="{ background: _leanColors[lean] }">
                  <option value="left">Sinistra</option>
                  <option value="center-left">Centro-sinistra</option>
                  <option value="center">Centro</option>
                  <option value="center-right">Centro-destra</option>
                  <option value="right">Destra</option>
                  <option value="international">Internazionale</option>
                  <option value="altro">Media neutri</option>
                </select>
                <svg class="pointer-events-none absolute right-1 top-1/2 -translate-y-1/2 w-3 h-3 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
              </div>
            </td>

            <!-- Tier -->
            <td class="px-3 py-3 text-center">
              <template x-if="!editing">
                <span class="text-xs font-bold text-gray-500" x-text="'T'+tier"></span>
              </template>
              <template x-if="editing">
                <select x-model.number="tier" class="border border-gray-300 px-2 py-1 text-sm w-16 focus:outline-none focus:border-[#C41E3A]">
                  <option value="1">T1</option>
                  <option value="2">T2</option>
                </select>
              </template>
            </td>

            <!-- Attiva -->
            <td class="px-3 py-3 text-center">
              <button @click="active = !active; patch({ active: active ? 1 : 0 })"
                      :class="active ? 'text-green-500 hover:text-green-700' : 'text-gray-300 hover:text-gray-500'"
                      title="Toggle attiva">
                <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </button>
            </td>

            <!-- Feed RSS -->
            <td class="px-4 py-3 hidden lg:table-cell">
              <template x-if="!editing">
                <span class="text-xs text-gray-400 truncate max-w-[200px] block" x-text="feedUrl || '—'"></span>
              </template>
              <template x-if="editing">
                <input x-model="feedUrl" type="url" placeholder="https://…"
                       class="border border-gray-300 px-2 py-1 text-xs w-52 focus:outline-none focus:border-[#C41E3A]">
              </template>
            </td>

            <!-- Articoli -->
            <td class="px-4 py-3 text-right text-xs text-gray-400 hidden md:table-cell">
              {{ number_format($source->articles_count) }}
            </td>

            <!-- Azioni -->
            <td class="px-3 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <template x-if="!editing">
                  <button @click="editing = true" class="text-gray-400 hover:text-gray-700 p-1" title="Modifica">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                </template>
                <template x-if="editing">
                  <button @click="save()" :disabled="saving"
                          class="text-xs font-bold px-2 py-1 bg-[#C41E3A] text-white hover:bg-red-800 disabled:opacity-50"
                          x-text="saving ? '…' : 'Salva'"></button>
                </template>
                <template x-if="editing">
                  <button @click="editing = false"
                          class="text-xs font-bold px-2 py-1 border border-gray-300 text-gray-600 hover:bg-gray-50">Annulla</button>
                </template>
                <template x-if="!editing">
                  <button @click="del()" :disabled="deleting" class="text-gray-300 hover:text-red-500 p-1" title="Elimina">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </template>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div>{{ $sources->links() }}</div>

  <!-- Modal: Aggiungi testata -->
  <div x-show="showCreate" x-cloak
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
       @keydown.escape.window="showCreate = false">
    <div class="bg-white w-full max-w-lg shadow-xl" @click.stop>
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="font-bold text-lg">Nuova testata</h2>
        <button @click="showCreate = false" class="text-gray-400 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="px-6 py-5 space-y-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Dominio *</label>
          <input x-model="newSource.domain" type="text" placeholder="es. corriere.it"
                 class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#C41E3A]">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Nome *</label>
          <input x-model="newSource.name" type="text" placeholder="es. Corriere della Sera"
                 class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#C41E3A]">
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Orientamento *</label>
            <select x-model="newSource.lean" class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#C41E3A]">
              @foreach ($leans as $l)
                <option value="{{ $l }}">{{ $leanLabels[$l] }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Tier *</label>
            <select x-model.number="newSource.tier" class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#C41E3A]">
              <option value="1">T1 — Nazionale principale</option>
              <option value="2">T2 — Regionale/nicchia</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Feed RSS URL</label>
          <input x-model="newSource.feed_url" type="url" placeholder="https://..."
                 class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#C41E3A]">
        </div>
        <p x-show="newSource.error" x-text="newSource.error" class="text-sm text-red-600" x-cloak></p>
      </div>
      <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
        <button @click="showCreate = false" class="px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">Annulla</button>
        <button @click="createSource()" :disabled="newSource.saving"
                class="px-4 py-2 bg-[#C41E3A] text-white text-sm font-semibold hover:bg-red-800 disabled:opacity-50"
                x-text="newSource.saving ? 'Salvataggio…' : 'Aggiungi'"></button>
      </div>
    </div>
  </div>

</div>

<script>
const _csrf       = document.querySelector('meta[name="csrf-token"]').content;

function sourceRow(id) {
  const d = window._sources[id];
  return {
    lean:       d.lean,
    tier:       d.tier,
    active:     d.active,
    feedUrl:    d.feedUrl,
    sourceName: d.name,
    updateUrl:  d.updateUrl,
    deleteUrl:  d.deleteUrl,
    editing:  false,
    saving:   false,
    deleting: false,

    patch(data) {
      return fetch(this.updateUrl, {
        method:  'PATCH',
        headers: { 'X-CSRF-TOKEN': _csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify(data),
      });
    },
    async save() {
      this.saving = true;
      await this.patch({ name: this.sourceName, political_lean: this.lean, tier: this.tier, active: this.active ? 1 : 0, feed_url: this.feedUrl || null });
      this.saving  = false;
      this.editing = false;
    },
    async del() {
      if (!confirm('Eliminare "' + this.sourceName + '"?')) return;
      this.deleting = true;
      await fetch(this.deleteUrl, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' } });
      this.$el.closest('tr').remove();
    },
  };
}

function sourcesAdmin() {
  return {
    showCreate: false,
    newSource: { domain: '', name: '', lean: 'altro', tier: 2, feed_url: '', saving: false, error: '' },
    async createSource() {
      this.newSource.saving = true;
      this.newSource.error  = '';
      const res  = await fetch('{{ route('admin.sources.create') }}', {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': _csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify({ domain: this.newSource.domain, name: this.newSource.name, political_lean: this.newSource.lean, tier: this.newSource.tier, feed_url: this.newSource.feed_url || null }),
      });
      const data = await res.json();
      this.newSource.saving = false;
      if (data.ok) { this.showCreate = false; window.location.reload(); }
      else { this.newSource.error = data.message ?? 'Errore.'; }
    },
  };
}
</script>
@endsection
