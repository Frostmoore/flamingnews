@extends('layouts.app')

@section('title', 'Articolo')
@section('requires_auth', true)

@section('content')
    <header class="border-b border-gray-300 bg-white sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">
            <a href="/" class="font-display text-2xl font-bold text-[#C41E3A] tracking-tight">FlamingNews</a>
        </div>
    </header>
    <div data-vue-component="ArticleDetail"></div>
@endsection
