<?php

use App\Http\Controllers\Gamification\GameController;
use App\Http\Controllers\Gamification\WebhookController;
use Illuminate\Support\Facades\Route;

// ── Webhooks (sans auth, sans CSRF, avec vérification signature + rate limiting) ──────────
Route::middleware(['twilio.webhook', 'throttle:60,1'])
    ->prefix('gamification/webhook')
    ->group(function () {
        Route::post('/{slug}/check',     [WebhookController::class, 'check']);
        Route::post('/{slug}/save-name', [WebhookController::class, 'saveName']);
        Route::post('/{slug}/answer',    [WebhookController::class, 'answer']);
        Route::post('/{slug}/complete',  [WebhookController::class, 'complete']);
    });

// ── Interface admin (avec auth) ───────────────────────────────────────────────
Route::middleware('auth')->prefix('gamification')->name('gamification.')->group(function () {
    Route::get('/',                                        [GameController::class, 'index'])->name('index');
    Route::get('/games/create',                            [GameController::class, 'create'])->name('create');
    Route::post('/games',                                  [GameController::class, 'store'])->name('store');
    Route::get('/games/{slug}',                            [GameController::class, 'show'])->name('show');
    Route::get('/games/{slug}/edit',                       [GameController::class, 'edit'])->name('edit');
    Route::put('/games/{slug}',                            [GameController::class, 'update'])->name('update');
    Route::delete('/games/{slug}',                         [GameController::class, 'destroy'])->name('destroy');
    Route::post('/games/{slug}/questions',                 [GameController::class, 'storeQuestion'])->name('questions.store');
    Route::delete('/games/{slug}/questions/{id}',          [GameController::class, 'destroyQuestion'])->name('questions.destroy');
    Route::post('/games/{slug}/activate',                  [GameController::class, 'activate'])->name('activate');
    Route::post('/games/{slug}/close',                     [GameController::class, 'close'])->name('close');
    Route::get('/games/{slug}/flow',                       [GameController::class, 'showFlow'])->name('flow');
    Route::get('/games/{slug}/export',                     [GameController::class, 'export'])->name('export');
    Route::get('/games/{slug}/statistics',                 [GameController::class, 'statistics'])->name('statistics');
    Route::get('/games/{slug}/leaderboard',                [GameController::class, 'leaderboard'])->name('leaderboard');
    Route::post('/games/{slug}/duplicate',                 [GameController::class, 'duplicate'])->name('duplicate');
    Route::post('/games/{slug}/answers/{answerId}/mark',   [GameController::class, 'markAnswer'])->name('answers.mark');
});
