<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLoginController;
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
Route::get('/login',    fn () => view('auth.login'))->name('login');
Route::get('/register', fn () => view('auth.register'));

// Profilo utente
Route::get('/profile',         fn () => view('pages.profile'));
Route::get('/my-feeds/{id}',   fn () => view('pages.my-feed-articles'));

// Analytics
Route::get('/analytics', fn () => view('pages.analytics'));

// ── Admin panel ──────────────────────────────────────────────────────────────
Route::get('/admin/login',  [AdminLoginController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout',[AdminLoginController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/',                          [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users',                     [AdminController::class, 'users'])->name('admin.users');
    Route::patch('/users/{user}',            [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{user}',           [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/sources',                   [AdminController::class, 'sources'])->name('admin.sources');
    Route::patch('/sources/{source}',        [AdminController::class, 'updateSource'])->name('admin.sources.update');
    Route::post('/sources',                  [AdminController::class, 'createSource'])->name('admin.sources.create');
    Route::delete('/sources/{source}',       [AdminController::class, 'deleteSource'])->name('admin.sources.delete');
    Route::post('/fetch',                    [AdminController::class, 'triggerFetch'])->name('admin.fetch');
});
