@extends('layouts.app')
@section('title', 'Accesso in corso...')

@section('content')
<div class="min-h-screen bg-[#F8F6F1] flex items-center justify-center" x-data x-init="handleCallback()">
  <div class="text-center">
    <div class="inline-block w-10 h-10 border-4 border-[#C41E3A] border-t-transparent rounded-full animate-spin mb-4"></div>
    <p class="text-gray-500 text-sm">Completamento accesso con Google...</p>
  </div>
</div>

<script>
function handleCallback() {
  const params = new URLSearchParams(window.location.search);
  const token = params.get('token');
  const needsCategories = params.get('needs_categories') === 'true';
  const error = params.get('error');

  if (error) {
    window.location.href = '/login?error=google_failed';
    return;
  }

  if (!token) {
    window.location.href = '/login';
    return;
  }

  // Salvo il token e recupero il profilo utente
  localStorage.setItem('fn_token', token);

  fetch('/api/auth/me', {
    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
  })
  .then(r => r.json())
  .then(data => {
    localStorage.setItem('fn_user', JSON.stringify(data.user));
    window.location.href = needsCategories ? '/auth/categories' : '/';
  })
  .catch(() => {
    window.location.href = '/';
  });
}
</script>
@endsection
