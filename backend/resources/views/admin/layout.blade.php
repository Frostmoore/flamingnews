<!DOCTYPE html>
<html lang="it" class="h-full bg-gray-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin — FlamingNews</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false }">

  <div class="flex h-full min-h-screen">

    <!-- Sidebar overlay mobile -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black/50 lg:hidden" x-cloak></div>

    <!-- Sidebar -->
    <aside
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      class="fixed inset-y-0 left-0 z-30 w-60 bg-[#1A1A1A] text-white flex flex-col transition-transform duration-200 lg:translate-x-0 lg:static lg:z-auto"
    >
      <!-- Logo -->
      <a href="/" class="flex items-center gap-2 px-5 py-4 border-b border-white/10">
        <span class="font-serif text-xl font-bold">Flaming<span class="text-[#C41E3A]">News</span></span>
        <span class="text-[10px] uppercase tracking-widest text-white/40 ml-1">Admin</span>
      </a>

      <!-- Nav -->
      <nav class="flex-1 py-4 space-y-0.5 px-2 overflow-y-auto">

        @php
          $nav = [
            ['route' => 'admin.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
            ['route' => 'admin.users',     'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'label' => 'Utenti'],
            ['route' => 'admin.sources',   'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z', 'label' => 'Testate'],
          ];
        @endphp

        @foreach ($nav as $item)
          <a href="{{ route($item['route']) }}"
             class="flex items-center gap-3 px-3 py-2.5 rounded text-sm font-medium transition-colors
                    {{ request()->routeIs($item['route']) ? 'bg-[#C41E3A] text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
            </svg>
            {{ $item['label'] }}
          </a>
        @endforeach
      </nav>

      <!-- Bottom: back to site + logout -->
      <div class="px-4 py-4 border-t border-white/10 space-y-2">
        <a href="/" class="flex items-center gap-2 text-xs text-white/50 hover:text-white transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Torna al sito
        </a>
        <form method="POST" action="{{ route('admin.logout') }}">
          @csrf
          <button type="submit" class="text-xs text-white/30 hover:text-white/60 transition-colors">Esci dall'admin</button>
        </form>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

      <!-- Topbar mobile -->
      <div class="lg:hidden flex items-center gap-3 px-4 py-3 bg-white border-b border-gray-200">
        <button @click="sidebarOpen = true" class="text-gray-500">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <span class="font-serif font-bold text-lg">Flaming<span class="text-[#C41E3A]">News</span> <span class="text-gray-400 text-sm font-sans font-normal">Admin</span></span>
      </div>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6">
        @yield('content')
      </main>
    </div>

  </div>

</body>
</html>
