<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TopicController;
use Illuminate\Support\Facades\Route;

// Autenticazione
Route::prefix('auth')->group(function () {
    // Email/password
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // Google OAuth (web)
    Route::get('/google/redirect',  [AuthController::class, 'googleRedirect']);
    Route::get('/google/callback',  [AuthController::class, 'googleCallback']);

    // Google OAuth (mobile — riceve id_token dal client)
    Route::post('/google/mobile',   [AuthController::class, 'googleMobile']);

    // Route protette da Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout',              [AuthController::class, 'logout']);
        Route::get('/me',                   [AuthController::class, 'me']);
        Route::patch('/categories',         [AuthController::class, 'updateCategories']);
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

// Analytics (pubblico)
Route::get('/analytics', [AnalyticsController::class, 'index']);
