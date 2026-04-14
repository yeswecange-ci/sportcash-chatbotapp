<?php

  use App\Http\Controllers\BotTracking\ConversationController;
  use App\Http\Controllers\BotTracking\ClientController;
  use App\Http\Controllers\BotTracking\WebhookController;
  use App\Http\Controllers\BotTracking\TwilioWebhookController;
  use Illuminate\Support\Facades\Route;

  // ── Endpoints bot (sans auth) ────────────────────────────────────────────────
  Route::prefix('bot-tracking/webhooks')->group(function () {
      Route::post('/event',     [WebhookController::class, 'handleEvent']);
      Route::post('/user-data', [WebhookController::class, 'updateUserData']);
      Route::post('/complete',  [WebhookController::class, 'handleComplete']);
  });

  Route::prefix('bot-tracking/twilio')->group(function () {
      Route::post('/incoming',    [TwilioWebhookController::class, 'handleIncomingMessage']);
      Route::post('/menu-choice', [TwilioWebhookController::class, 'handleMenuChoice']);
      Route::post('/free-input',  [TwilioWebhookController::class, 'handleFreeInput']);
      Route::post('/complete',    [TwilioWebhookController::class, 'completeConversation']);
  });

  // ── Interface web (avec auth) ────────────────────────────────────────────────
  Route::middleware('auth')->prefix('bot-tracking')->name('bot-tracking.')->group(function () {

      // Sous-menu : Bot
      Route::get('/',                   [ConversationController::class, 'index'])->name('index');
      Route::get('/active',             [ConversationController::class, 'active'])->name('active');
      Route::get('/conversations',      [ConversationController::class, 'conversations'])->name('conversations');
      Route::get('/conversations/{id}', [ConversationController::class, 'show'])->name('conversations.show');

      // Sous-menu : Clients
      Route::prefix('clients')->name('clients.')->group(function () {
          Route::get('/',          [ClientController::class, 'index'])->name('index');
          Route::get('/sync',      [ClientController::class, 'sync'])->name('sync')->middleware('role:super_admin,admin');
          Route::get('/{id}',      [ClientController::class, 'show'])->name('show');
          Route::get('/{id}/edit', [ClientController::class, 'edit'])->name('edit')->middleware('role:super_admin,admin');
          Route::put('/{id}',      [ClientController::class, 'update'])->name('update')->middleware('role:super_admin,admin');
      });

      // Sous-menu : Analytics
      Route::get('/statistics', [ConversationController::class, 'statistics'])->name('statistics');
      Route::get('/search',     [ConversationController::class, 'search'])->name('search');
  });