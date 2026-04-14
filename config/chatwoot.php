<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chatwoot API Configuration
    |--------------------------------------------------------------------------
    */

    // URL de votre instance Chatwoot self-hosted (sans slash final)
    'base_url' => env('CHATWOOT_BASE_URL', 'https://support.yeswechange.com'),

    // ID du compte Chatwoot
    'account_id' => env('CHATWOOT_ACCOUNT_ID', 1),

    // Token d'accès API (user_access_token depuis Profile Settings)
    'api_token' => env('CHATWOOT_API_TOKEN'),

    // Platform API token (pour gestion multi-tenant future)
    'platform_token' => env('CHATWOOT_PLATFORM_TOKEN'),

    // ID de l'inbox WhatsApp / API Channel
    'whatsapp_inbox_id' => env('CHATWOOT_WHATSAPP_INBOX_ID'),

    // Intervalle de polling en millisecondes (frontend)
    'polling_interval' => env('CHATWOOT_POLLING_INTERVAL', 4000),

    // Secret pour valider les webhooks Chatwoot
    'webhook_secret' => env('CHATWOOT_WEBHOOK_SECRET'),

];
