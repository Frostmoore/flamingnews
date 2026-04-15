<?php

use App\Http\Controllers\EmailVerificationController;
use Illuminate\Support\Facades\Route;

// Verifica email
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::get('/email-sent',      fn () => view('auth.email-sent'));
Route::get('/forgot-password', fn () => view('auth.forgot-password'));
Route::get('/reset-password',  fn () => view('auth.reset-password'))->name('password.reset');

// Callback OAuth — il token arriva come query param, il JS lo salva
Route::get('/auth/callback',    fn () => view('auth.callback'));
Route::get('/auth/categories',  fn () => view('auth.categories'));

// Pagine pubbliche (richiedono JS auth guard)
Route::get('/',               fn () => view('pages.home'));
Route::get('/coverage',       fn () => view('pages.coverage'));
Route::get('/articles/{id}',  fn () => view('pages.article'));

// Auth
Route::get('/login',    fn () => view('auth.login'));
Route::get('/register', fn () => view('auth.register'));

// Profilo utente
Route::get('/profile', fn () => view('pages.profile'));

// Analytics
Route::get('/analytics', fn () => view('pages.analytics'));
