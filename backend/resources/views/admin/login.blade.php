<!DOCTYPE html>
<html lang="it" class="h-full bg-[#1A1A1A]">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — FlamingNews</title>
  @vite(['resources/css/app.css'])
</head>
<body class="h-full flex items-center justify-center">
  <div class="w-full max-w-sm px-4">

    <div class="text-center mb-8">
      <span class="font-serif text-3xl font-bold text-white">Flaming<span class="text-[#C41E3A]">News</span></span>
      <p class="text-xs uppercase tracking-widest text-white/30 mt-1">Area amministrazione</p>
    </div>

    <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-4">
      @csrf
      <div>
        <input
          type="password"
          name="password"
          placeholder="Password"
          autofocus
          class="w-full bg-white/10 border border-white/20 text-white placeholder-white/30 px-4 py-3 text-sm focus:outline-none focus:border-[#C41E3A] transition-colors"
        >
        @error('password')
          <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
        @enderror
      </div>
      <button type="submit"
              class="w-full bg-[#C41E3A] text-white font-bold text-sm py-3 hover:bg-red-800 transition-colors tracking-wide uppercase">
        Accedi
      </button>
    </form>

    <div class="text-center mt-6">
      <a href="/" class="text-xs text-white/25 hover:text-white/50 transition-colors">← Torna al sito</a>
    </div>

  </div>
</body>
</html>
