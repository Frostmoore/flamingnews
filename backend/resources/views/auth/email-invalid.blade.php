@extends('layouts.app')
@section('title', 'Link non valido')

@section('content')
<div class="min-h-screen bg-[#F8F6F1] flex items-center justify-center p-4">
  <div class="w-full max-w-sm text-center">

    <a href="/" class="block font-display text-3xl font-bold text-[#C41E3A] text-center mb-8 tracking-tight">
      FlamingNews
    </a>

    <div class="bg-white border border-gray-200 p-8 shadow-sm">
      <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-6">
        <svg class="w-8 h-8 text-[#C41E3A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
      </div>

      <h1 class="text-xl font-bold text-[#1A1A1A] mb-3">Link non valido o scaduto</h1>
      <p class="text-sm text-gray-500 leading-relaxed mb-6">
        Il link di verifica è scaduto o non è più valido.<br>
        Prova ad accedere: ti invieremo automaticamente un nuovo link.
      </p>

      <a href="/login" class="block w-full bg-[#C41E3A] text-white py-2.5 text-sm font-bold tracking-wide hover:bg-red-800 transition-colors text-center">
        Vai al login
      </a>
    </div>

  </div>
</div>
@endsection
