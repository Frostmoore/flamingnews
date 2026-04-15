<?php

use Illuminate\Support\Facades\Route;

// Callback OAuth — il token arriva come query param, il JS lo salva
Route::get('/auth/callback',    fn () => view('auth.callback'));
Route::get('/auth/categories',  fn () => view('auth.categories'));

// Pagine pubbliche (richiedono JS auth guard)
Route::get('/',               fn () => view('pages.home'));
Route::get('/prime-pagine',   fn () => view('pages.prime-pagine'));
Route::get('/coverage',       fn () => view('pages.coverage'));
Route::get('/articles/{id}',  fn () => view('pages.article'));

// Auth
Route::get('/login',    fn () => view('auth.login'));
Route::get('/register', fn () => view('auth.register'));
