<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\CannedResponseController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignContactController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Kash\DashboardController as KashDashboardController;
use App\Http\Controllers\Kash\ReclamationController;
use App\Http\Controllers\Kash\EscaladeController;
use App\Http\Controllers\Kash\HistoriqueController;
use Illuminate\Support\Facades\Route;

// ── Route principale → login ──────────────────────────
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return auth()->user()->isAdmin()
        ? redirect()->route('dashboard')
        : redirect()->route('conversations.index');
});

// ── App (authentifié) ─────────────────────────────────
Route::middleware('auth')->group(function () {

    // Conversations (accessible a tous)
    Route::get('/conversations', [ConversationController::class, 'index'])
        ->name('conversations.index');
    Route::get('/conversations/{conversationId}', [ConversationController::class, 'show'])
        ->name('conversations.show');

    // Canned Responses (accessible a tous)
    Route::get('/canned-responses', [CannedResponseController::class, 'index'])
        ->name('canned-responses.index');

    // Contacts (accessible a tous)
    Route::get('/contacts', [ContactController::class, 'index'])
        ->name('contacts.index');

    // Admin only pages
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    });

    // AJAX — Polling & Actions
    Route::prefix('ajax')->group(function () {
        // Conversations (static routes FIRST)
        Route::get('/conversations/counts', [ConversationController::class, 'counts'])
            ->name('ajax.counts');
        Route::get('/conversations/list-update', [ConversationController::class, 'listUpdate'])
            ->name('ajax.listUpdate');
        Route::post('/conversations/filter', [ConversationController::class, 'filter'])
            ->name('ajax.filter');
        // Conversations (dynamic routes)
        Route::get('/conversations/{conversationId}/panel', [ConversationController::class, 'panel'])
            ->name('ajax.panel');
        Route::get('/conversations/{conversationId}/poll', [ConversationController::class, 'poll'])
            ->name('ajax.poll');
        Route::post('/conversations/{conversationId}/messages', [ConversationController::class, 'sendMessage'])
            ->name('ajax.send');
        Route::post('/conversations/{conversationId}/status', [ConversationController::class, 'toggleStatus'])
            ->name('ajax.status');
        Route::post('/conversations/{conversationId}/read', [ConversationController::class, 'markRead'])
            ->name('ajax.read');
        Route::post('/conversations/{conversationId}/typing', [ConversationController::class, 'typing'])
            ->name('ajax.typing');
        Route::get('/conversations/{conversationId}/messages-before', [ConversationController::class, 'messagesBefore'])
            ->name('ajax.messagesBefore');
        Route::post('/conversations/{conversationId}/assign', [ConversationController::class, 'assign'])
            ->name('ajax.assign');
        Route::delete('/conversations/{conversationId}/messages/{messageId}', [ConversationController::class, 'deleteMessage'])
            ->name('ajax.deleteMessage');
        Route::delete('/conversations/{conversationId}', [ConversationController::class, 'destroy'])
            ->name('ajax.deleteConversation');

        // Twilio Templates
        Route::get('/twilio/templates', [ConversationController::class, 'twilioTemplates'])
            ->name('ajax.twilio.templates');
        Route::post('/conversations/{conversationId}/template-message', [ConversationController::class, 'sendTemplateMessage'])
            ->name('ajax.sendTemplate');

        // Labels
        Route::get('/labels', [LabelController::class, 'list'])->name('ajax.labels');
        Route::post('/labels', [LabelController::class, 'store'])->name('ajax.labels.store');
        Route::get('/conversations/{conversationId}/labels', [LabelController::class, 'conversationLabels'])
            ->name('ajax.conversation.labels');
        Route::post('/conversations/{conversationId}/labels', [LabelController::class, 'updateConversationLabels'])
            ->name('ajax.conversation.labels.update');

        // Canned Responses
        Route::get('/canned-responses', [CannedResponseController::class, 'list'])
            ->name('ajax.canned');
        Route::post('/canned-responses', [CannedResponseController::class, 'store'])
            ->name('ajax.canned.store');
        Route::put('/canned-responses/{id}', [CannedResponseController::class, 'update'])
            ->name('ajax.canned.update');
        Route::delete('/canned-responses/{id}', [CannedResponseController::class, 'destroy'])
            ->name('ajax.canned.destroy');

        // Contacts
        Route::get('/contacts/search', [ContactController::class, 'search'])
            ->name('ajax.contact.search');
        Route::post('/contacts', [ContactController::class, 'store'])
            ->name('ajax.contact.store');
        Route::get('/contacts/{contactId}', [ContactController::class, 'show'])
            ->name('ajax.contact');
        Route::put('/contacts/{contactId}', [ContactController::class, 'update'])
            ->name('ajax.contact.update');
        Route::get('/contacts/{contactId}/conversations', [ContactController::class, 'conversations'])
            ->name('ajax.contact.conversations');
        Route::get('/contacts/{contactId}/notes', [ContactController::class, 'notes'])
            ->name('ajax.contact.notes');
        Route::post('/contacts/{contactId}/notes', [ContactController::class, 'storeNote'])
            ->name('ajax.contact.notes.store');
        Route::delete('/contacts/{contactId}/notes/{noteId}', [ContactController::class, 'destroyNote'])
            ->name('ajax.contact.notes.destroy');
        Route::delete('/contacts/{contactId}', [ContactController::class, 'destroy'])
            ->name('ajax.contact.destroy');
        Route::post('/contacts/{contactId}/send-template', [ContactController::class, 'sendTemplate'])
            ->name('ajax.contact.sendTemplate');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'list'])
            ->name('ajax.notifications');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])
            ->name('ajax.notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
            ->name('ajax.notifications.readAll');

        // Teams (list + assign accessible a tous, CRUD admin only)
        Route::get('/teams', [TeamController::class, 'list'])->name('ajax.teams');
        Route::post('/conversations/{conversationId}/team', [TeamController::class, 'assignTeam'])
            ->name('ajax.assign.team');

        // Admin-only AJAX routes
        Route::middleware('admin')->group(function () {
            // Teams CRUD
            Route::post('/teams', [TeamController::class, 'store'])->name('ajax.teams.store');
            Route::put('/teams/{teamId}', [TeamController::class, 'update'])->name('ajax.teams.update');
            Route::delete('/teams/{teamId}', [TeamController::class, 'destroy'])->name('ajax.teams.destroy');
            Route::post('/teams/{teamId}/members', [TeamController::class, 'addMembers'])->name('ajax.teams.members.add');
            Route::delete('/teams/{teamId}/members', [TeamController::class, 'removeMembers'])->name('ajax.teams.members.remove');

            // Agents CRUD
            Route::post('/agents', [AgentController::class, 'store'])->name('ajax.agents.store');
            Route::put('/agents/{agentId}', [AgentController::class, 'update'])->name('ajax.agents.update');
            Route::delete('/agents/{agentId}', [AgentController::class, 'destroy'])->name('ajax.agents.destroy');

            // Inboxes settings
            Route::get('/inboxes', [InboxController::class, 'list'])->name('ajax.inboxes');
            Route::get('/inboxes/{inboxId}/settings', [InboxController::class, 'getSettings'])->name('ajax.inboxes.settings');
            Route::post('/inboxes/{inboxId}/auto-assignment', [InboxController::class, 'updateAutoAssignment'])->name('ajax.inboxes.autoAssign');

            // Statistics
            Route::get('/statistics/data', [StatisticsController::class, 'data'])->name('ajax.statistics.data');
            Route::post('/statistics/sync', [StatisticsController::class, 'syncStats'])->name('ajax.statistics.sync');

            // Dashboard
            Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('ajax.dashboard.data');
            Route::post('/dashboard/sync', [DashboardController::class, 'syncStats'])->name('ajax.dashboard.sync');

            // Settings
            Route::post('/settings/update', [SettingsController::class, 'update'])->name('ajax.settings.update');
            Route::post('/settings/test-chatwoot', [SettingsController::class, 'testChatwoot'])->name('ajax.settings.testChatwoot');
            Route::post('/settings/test-twilio', [SettingsController::class, 'testTwilio'])->name('ajax.settings.testTwilio');
            Route::post('/settings/test-database', [SettingsController::class, 'testDatabase'])->name('ajax.settings.testDatabase');
            Route::get('/settings/logs', [SettingsController::class, 'getLogs'])->name('ajax.settings.logs');
            Route::delete('/settings/logs', [SettingsController::class, 'clearLogs'])->name('ajax.settings.clearLogs');
        });

        // ── Campagnes AJAX (accessible a tous) ───────────
        Route::post('/campagnes', [CampaignController::class, 'store'])->name('ajax.campagnes.store');
        Route::put('/campagnes/{campaign}', [CampaignController::class, 'update'])->name('ajax.campagnes.update');
        Route::delete('/campagnes/{campaign}', [CampaignController::class, 'destroy'])->name('ajax.campagnes.destroy');
        Route::post('/campagnes/{campaign}/send', [CampaignController::class, 'sendPush'])->name('ajax.campagnes.send');
        Route::post('/campagnes/{campaign}/schedule', [CampaignController::class, 'schedulePush'])->name('ajax.campagnes.schedule');
        Route::delete('/campagnes/{campaign}/schedule', [CampaignController::class, 'cancelSchedule'])->name('ajax.campagnes.cancelSchedule');
        Route::post('/campagnes/{campaign}/reopen', [CampaignController::class, 'reopen'])->name('ajax.campagnes.reopen');
        Route::post('/campagnes/{campaign}/send-single', [CampaignController::class, 'sendSingle'])->name('ajax.campagnes.sendSingle');
        Route::post('/campagnes/{campaign}/contacts', [CampaignController::class, 'attachContacts'])->name('ajax.campagnes.attachContacts');
        Route::delete('/campagnes/{campaign}/contacts', [CampaignController::class, 'detachContacts'])->name('ajax.campagnes.detachContacts');
        Route::get('/campagnes/{campaign}/contacts', [CampaignController::class, 'listContacts'])->name('ajax.campagnes.listContacts');
        Route::get('/campagnes/{campaign}/messages', [CampaignController::class, 'listMessages'])->name('ajax.campagnes.listMessages');
        Route::get('/campagnes/{campaign}/stats', [CampaignController::class, 'stats'])->name('ajax.campagnes.stats');
        Route::post('/campagnes/{campaign}/refresh-statuses', [CampaignController::class, 'refreshStatuses'])->name('ajax.campagnes.refreshStatuses');
        Route::get('/campagnes/{campaign}/available-contacts', [CampaignController::class, 'searchAvailableContacts'])->name('ajax.campagnes.availableContacts');
        Route::get('/campagnes/{campaign}/period-stats', [CampaignController::class, 'periodStats'])->name('ajax.campagnes.periodStats');
        Route::get('/campagnes-dashboard/data', [CampaignController::class, 'dashboardData'])->name('ajax.campagnes.dashboard.data');

        // Contacts Campagne AJAX
        Route::get('/campagnes-contacts/search', [CampaignContactController::class, 'search'])->name('ajax.campagnes.contacts.search');
        Route::post('/campagnes-contacts', [CampaignContactController::class, 'store'])->name('ajax.campagnes.contacts.store');
        Route::put('/campagnes-contacts/{contact}', [CampaignContactController::class, 'update'])->name('ajax.campagnes.contacts.update');
        Route::delete('/campagnes-contacts/{contact}', [CampaignContactController::class, 'destroy'])->name('ajax.campagnes.contacts.destroy');
        Route::post('/campagnes-contacts/import-preview', [CampaignContactController::class, 'importPreview'])->name('ajax.campagnes.contacts.importPreview');
        Route::post('/campagnes-contacts/import-confirm', [CampaignContactController::class, 'importConfirm'])->name('ajax.campagnes.contacts.importConfirm');
        Route::post('/campagnes-contacts/{contact}/sync-chatwoot', [CampaignContactController::class, 'syncToChatwoot'])->name('ajax.campagnes.contacts.syncChatwoot');
        Route::post('/campagnes-contacts/import-chatwoot', [CampaignContactController::class, 'importFromChatwoot'])->name('ajax.campagnes.contacts.importChatwoot');
    });

    // ── Campagnes (accessible a tous) ───────────────────
    Route::get('/campagnes', [CampaignController::class, 'index'])->name('campagnes.index');
    Route::get('/campagnes/create', [CampaignController::class, 'create'])->name('campagnes.create');
    Route::get('/campagnes/dashboard', [CampaignController::class, 'dashboard'])->name('campagnes.dashboard');
    Route::get('/campagnes/{campaign}', [CampaignController::class, 'show'])->name('campagnes.show');
    Route::get('/campagnes/{campaign}/edit', [CampaignController::class, 'edit'])->name('campagnes.edit');

    // Contacts Campagnes
    Route::get('/campagnes-contacts', [CampaignContactController::class, 'index'])->name('campagnes.contacts.index');
    Route::get('/campagnes-contacts/import', [CampaignContactController::class, 'importForm'])->name('campagnes.contacts.import');

    // ── Kash Bot ─────────────────────────────────────────
    Route::prefix('kash')->name('kash.')->group(function () {
        Route::get('/', [KashDashboardController::class, 'index'])->name('dashboard');

        // Réclamations
        Route::get('/reclamations', [ReclamationController::class, 'index'])->name('reclamations.index');
        Route::get('/reclamations/{reclamation:reference}', [ReclamationController::class, 'show'])->name('reclamations.show');
        Route::patch('/reclamations/{reclamation}/statut', [ReclamationController::class, 'updateStatut'])->name('reclamations.statut');
        Route::patch('/reclamations/{reclamation}/assign', [ReclamationController::class, 'assign'])->name('reclamations.assign');
        Route::patch('/reclamations/{reclamation}/notes', [ReclamationController::class, 'updateNotes'])->name('reclamations.notes');

        // Historique conversations bot
        Route::get('/historique', [HistoriqueController::class, 'index'])->name('historique');

        // Escalades
        Route::get('/escalades', [EscaladeController::class, 'index'])->name('escalades.index');
        Route::get('/escalades/{escalade:reference}', [EscaladeController::class, 'show'])->name('escalades.show');
        Route::patch('/escalades/{escalade}/statut', [EscaladeController::class, 'updateStatut'])->name('escalades.statut');
        Route::patch('/escalades/{escalade}/assign', [EscaladeController::class, 'assign'])->name('escalades.assign');
        Route::patch('/escalades/{escalade}/notes', [EscaladeController::class, 'updateNotes'])->name('escalades.notes');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // AJAX — Agent availability
    Route::post('/ajax/profile/availability', [ProfileController::class, 'toggleAvailability'])
        ->name('ajax.availability');
    Route::get('/ajax/profile/availability', [ProfileController::class, 'getAvailability'])
        ->name('ajax.availability.get');

    // AJAX — Profile modal updates
    Route::post('/ajax/profile/update', [ProfileController::class, 'updateAjax'])
        ->name('ajax.profile.update');
    Route::post('/ajax/profile/password', [ProfileController::class, 'updatePasswordAjax'])
        ->name('ajax.profile.password');
});

require __DIR__.'/auth.php';
