<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\UserFeedController;
use Illuminate\Support\Facades\Route;

// Autenticazione
Route::prefix('auth')->group(function () {
    // Email/password
    Route::post('/register',        [AuthController::class, 'register']);
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

    // Google OAuth (web)
    Route::get('/google/redirect',  [AuthController::class, 'googleRedirect']);
    Route::get('/google/callback',  [AuthController::class, 'googleCallback']);

    // Google OAuth (mobile — riceve id_token dal client)
    Route::post('/google/mobile',   [AuthController::class, 'googleMobile']);

    // Route protette da Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout',              [AuthController::class, 'logout']);
        Route::get('/me',                   [AuthController::class, 'me']);
        Route::patch('/profile',            [AuthController::class, 'updateProfile']);
        Route::patch('/password',           [AuthController::class, 'updatePassword']);
        Route::patch('/categories',         [AuthController::class, 'updateCategories']);
        Route::patch('/sources',            [AuthController::class, 'updateSources']);
    });
});

// Articoli (pubblico con letture tracciate se autenticato)
Route::get('/articles',      [ArticleController::class, 'index'])->middleware('auth:sanctum')->withoutMiddleware('auth:sanctum');
Route::get('/articles/{id}', [ArticleController::class, 'show'])->middleware('auth:sanctum')->withoutMiddleware('auth:sanctum');
Route::post('/articles/{id}/like',  [ArticleController::class, 'like'])->middleware('auth:sanctum');
Route::post('/articles/{id}/share', [ArticleController::class, 'share']);
Route::post('/articles/{id}/click', [ArticleController::class, 'click']);

// Topics (pubblico)
Route::get('/topics',       [TopicController::class, 'index']);
Route::get('/topics/{id}',  [TopicController::class, 'show']);

// Analisi AI (solo Premium, richiede autenticazione)
Route::post('/topics/{id}/analyze', [TopicController::class, 'analyze'])->middleware('auth:sanctum');

// Testate attive con feed RSS (pubblico — usato nella registrazione)
Route::get('/sources', fn () => response()->json(
    \Illuminate\Support\Facades\DB::table('sources')
        ->where('active', true)
        ->whereNotNull('feed_url')
        ->select('domain', 'name', 'political_lean', 'tier')
        ->orderBy('tier')->orderBy('name')
        ->get()
));

// Feed RSS personali (solo autenticati)
Route::middleware('auth:sanctum')->prefix('my-feeds')->group(function () {
    Route::get('/',              [UserFeedController::class, 'index']);
    Route::post('/',             [UserFeedController::class, 'store']);
    Route::delete('/{id}',       [UserFeedController::class, 'destroy']);
    Route::get('/articles',      [UserFeedController::class, 'articles']);
    Route::post('/{id}/refresh', [UserFeedController::class, 'refresh']);
});

// Analytics (pubblico)
Route::get('/analytics',        [AnalyticsController::class, 'index']);
Route::get('/analytics/source', [AnalyticsController::class, 'source']);
Route::get('/articles/{id}/coverage', [ArticleController::class, 'coverage']);
