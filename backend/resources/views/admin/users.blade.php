@extends('admin.layout')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="usersAdmin()">

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Utenti</h1>
      <p class="text-sm text-gray-500 mt-1">{{ $users->total() }} utenti registrati.</p>
    </div>
    <form method="GET" class="flex gap-2">
      <input type="search" name="q" value="{{ $q }}" placeholder="Cerca per nome, email…"
             class="border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#C41E3A] w-60">
      <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold hover:bg-gray-900">
        Cerca
      </button>
    </form>
  </div>

  <!-- Tabella -->
  <div class="bg-white border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-500">Utente</th>
            <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-500 hidden md:table-cell">Email</th>
            <th class="text-center px-3 py-3 text-xs font-bold uppercase tracking-widest text-gray-500">Verif.</th>
            <th class="text-center px-3 py-3 text-xs font-bold uppercase tracking-widest text-gray-500">Premium</th>
            <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-500 hidden lg:table-cell">Registrato</th>
            <th class="px-3 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach ($users as $user)
          <tr class="hover:bg-gray-50 transition-colors" x-data="{
            isPremium:  {{ $user->is_premium ? 'true' : 'false' }},
            isVerified: {{ $user->email_verified_at ? 'true' : 'false' }},
            saving: false,
            async toggle(field, val) {
              this.saving = true;
              const body = {}; body[field] = val ? 1 : 0;
              await fetch('{{ route('admin.users.update', $user) }}', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(body),
              });
              this.saving = false;
            },
            async del() {
              if (!confirm('Eliminare {{ addslashes($user->name) }}?')) return;
              await fetch('{{ route('admin.users.delete', $user) }}', {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
              });
              $el.closest('tr').remove();
            },
          }">
            <td class="px-4 py-3">
              <div class="font-semibold text-gray-900">{{ $user->name }}</div>
              <div class="text-xs text-gray-400">@{{ $user->username ?? '—' }}</div>
            </td>
            <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ $user->email }}</td>

            <!-- Verificato -->
            <td class="px-3 py-3 text-center">
              <button @click="isVerified = !isVerified; toggle('email_verified', isVerified)"
                      :class="isVerified ? 'text-green-500' : 'text-gray-300 hover:text-gray-400'"
                      :disabled="saving" title="Toggle verifica email">
                <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </button>
            </td>

            <!-- Premium -->
            <td class="px-3 py-3 text-center">
              <button @click="isPremium = !isPremium; toggle('is_premium', isPremium)"
                      :class="isPremium ? 'text-yellow-500' : 'text-gray-300 hover:text-gray-400'"
                      :disabled="saving" title="Toggle premium">
                <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
              </button>
            </td>

            <td class="px-4 py-3 text-xs text-gray-400 hidden lg:table-cell">
              {{ $user->created_at->format('d/m/Y') }}
            </td>

            <td class="px-3 py-3 text-right">
              @if (! $user->is_admin)
              <button @click="del()" class="text-gray-300 hover:text-red-500 transition-colors" title="Elimina">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- Paginazione -->
  <div>{{ $users->links() }}</div>

</div>

<script>
function usersAdmin() { return {}; }
</script>
@endsection
