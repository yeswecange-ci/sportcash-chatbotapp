@extends('layouts.app')

@section('title', 'Paramètres système')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">Paramètres système</h1>
                    <p class="text-xs text-gray-500">Configuration, connexions et logs d'erreur</p>
                </div>
            </div>
            <button onclick="testAll()" id="btn-test-all"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Tout tester
            </button>
        </div>
    </div>

    <div class="p-6 max-w-5xl mx-auto space-y-6">

        {{-- ─── Cartes de statut ─────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Chatwoot --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5" id="card-chatwoot">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Chatwoot</p>
                            <p class="text-xs text-gray-400">API Support Client</p>
                        </div>
                    </div>
                    <span id="status-chatwoot" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Non testé
                    </span>
                </div>
                <p id="detail-chatwoot" class="text-xs text-gray-400 min-h-[16px]">{{ $config['chatwoot']['base_url'] }}</p>
                <button onclick="testConnection('chatwoot')"
                        class="mt-3 w-full py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                    Tester la connexion
                </button>
            </div>

            {{-- Twilio --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5" id="card-twilio">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Twilio</p>
                            <p class="text-xs text-gray-400">API WhatsApp</p>
                        </div>
                    </div>
                    <span id="status-twilio" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Non testé
                    </span>
                </div>
                <p id="detail-twilio" class="text-xs text-gray-400 min-h-[16px]">{{ $config['twilio']['whatsapp_from'] ?: 'Non configuré' }}</p>
                <button onclick="testConnection('twilio')"
                        class="mt-3 w-full py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                    Tester la connexion
                </button>
            </div>

            {{-- Base de données --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5" id="card-database">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Base de données</p>
                            <p class="text-xs text-gray-400">{{ strtoupper($config['database']['connection']) }}</p>
                        </div>
                    </div>
                    <span id="status-database" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Non testé
                    </span>
                </div>
                <p id="detail-database" class="text-xs text-gray-400 min-h-[16px]">{{ $config['database']['username'] }}@{{ $config['database']['host'] }}/{{ $config['database']['database'] }}</p>
                <button onclick="testConnection('database')"
                        class="mt-3 w-full py-1.5 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition">
                    Tester la connexion
                </button>
            </div>
        </div>

        {{-- ─── Onglets de configuration ─────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            {{-- Tab header --}}
            <div class="border-b border-gray-200 flex">
                <button onclick="switchTab('chatwoot')" id="tab-chatwoot"
                        class="tab-btn active-tab px-5 py-3 text-sm font-medium border-b-2 border-indigo-600 text-indigo-600 transition">
                    Chatwoot
                </button>
                <button onclick="switchTab('twilio')" id="tab-twilio"
                        class="tab-btn px-5 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                    Twilio
                </button>
                <button onclick="switchTab('app')" id="tab-app"
                        class="tab-btn px-5 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                    Application
                </button>
                <button onclick="switchTab('logs')" id="tab-logs"
                        class="tab-btn px-5 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                    Logs
                </button>
                <button onclick="switchTab('twilio-flow')" id="tab-twilio-flow"
                        class="tab-btn px-5 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition">
                    Twilio Flow API
                </button>
            </div>

            {{-- ── Tab Chatwoot ────────────────────────────────── --}}
            <div id="panel-chatwoot" class="tab-panel p-6">
                <p class="text-xs text-gray-500 mb-5">Connexion à votre instance Chatwoot. Après modification, la connexion doit être retestée.</p>
                <form onsubmit="saveSection(event, 'chatwoot')" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">URL de l'instance Chatwoot</label>
                            <input type="url" name="CHATWOOT_BASE_URL" value="{{ $config['chatwoot']['base_url'] }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="https://support.example.com">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Account ID</label>
                            <input type="text" name="CHATWOOT_ACCOUNT_ID" value="{{ $config['chatwoot']['account_id'] }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="1">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">WhatsApp Inbox ID</label>
                            <input type="text" name="CHATWOOT_WHATSAPP_INBOX_ID" value="{{ $config['chatwoot']['whatsapp_inbox_id'] }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="ID de l'inbox WhatsApp">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">API Token</label>
                            <div class="relative">
                                <input type="password" name="CHATWOOT_API_TOKEN" value="{{ $config['chatwoot']['api_token'] }}"
                                       id="field-chatwoot-token"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent pr-10"
                                       placeholder="user_access_token">
                                <button type="button" onclick="toggleReveal('field-chatwoot-token')"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Webhook Secret</label>
                            <div class="relative">
                                <input type="password" name="CHATWOOT_WEBHOOK_SECRET" value="{{ $config['chatwoot']['webhook_secret'] }}"
                                       id="field-chatwoot-webhook"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent pr-10"
                                       placeholder="Secret webhook">
                                <button type="button" onclick="toggleReveal('field-chatwoot-webhook')"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Intervalle de polling (ms)</label>
                            <input type="number" name="CHATWOOT_POLLING_INTERVAL" value="{{ $config['chatwoot']['polling_interval'] }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   min="1000" max="60000" step="1000">
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit"
                                class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4 save-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="save-label">Enregistrer</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Tab Twilio ──────────────────────────────────── --}}
            <div id="panel-twilio" class="tab-panel hidden p-6">
                <p class="text-xs text-gray-500 mb-5">Identifiants Twilio pour l'envoi de messages WhatsApp.</p>
                <form onsubmit="saveSection(event, 'twilio')" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Account SID</label>
                            <input type="text" name="TWILIO_SID" value="{{ $config['twilio']['sid'] }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Numéro WhatsApp (From)</label>
                            <input type="text" name="TWILIO_WHATSAPP_FROM" value="{{ $config['twilio']['whatsapp_from'] }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="whatsapp:+14155238886">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Auth Token</label>
                            <div class="relative">
                                <input type="password" name="TWILIO_AUTH_TOKEN" value="{{ $config['twilio']['auth_token'] }}"
                                       id="field-twilio-token"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent pr-10"
                                       placeholder="Auth token Twilio">
                                <button type="button" onclick="toggleReveal('field-twilio-token')"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit"
                                class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4 save-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="save-label">Enregistrer</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Tab Application ─────────────────────────────── --}}
            <div id="panel-app" class="tab-panel hidden p-6">
                <p class="text-xs text-gray-500 mb-5">Paramètres généraux de l'application.</p>
                <form onsubmit="saveSection(event, 'app')" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nom de l'application</label>
                            <input type="text" name="APP_NAME" value="{{ $config['app']['name'] }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">URL de l'application</label>
                            <input type="url" name="APP_URL" value="{{ $config['app']['url'] }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Environnement</label>
                            <select name="APP_ENV"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="local" {{ $config['app']['env'] === 'local' ? 'selected' : '' }}>Local (développement)</option>
                                <option value="production" {{ $config['app']['env'] === 'production' ? 'selected' : '' }}>Production</option>
                                <option value="staging" {{ $config['app']['env'] === 'staging' ? 'selected' : '' }}>Staging</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Fuseau horaire</label>
                            <input type="text" name="APP_TIMEZONE" value="{{ $config['app']['timezone'] }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="Africa/Abidjan">
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="text-xs font-medium text-gray-700">Mode debug</label>
                            <button type="button" onclick="toggleDebug(this)"
                                    data-on="{{ $config['app']['debug'] ? 'true' : 'false' }}"
                                    class="debug-toggle relative w-10 h-5 rounded-full transition-colors {{ $config['app']['debug'] ? 'bg-red-500' : 'bg-gray-300' }}">
                                <span class="absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform {{ $config['app']['debug'] ? 'translate-x-5' : 'translate-x-0.5' }}"></span>
                            </button>
                            <input type="hidden" name="APP_DEBUG" id="app-debug-value" value="{{ $config['app']['debug'] ? 'true' : 'false' }}">
                            <span id="debug-label" class="text-xs {{ $config['app']['debug'] ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                                {{ $config['app']['debug'] ? 'Activé (attention !)' : 'Désactivé' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit"
                                class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4 save-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="save-label">Enregistrer</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Tab Logs ─────────────────────────────────────── --}}
            <div id="panel-logs" class="tab-panel hidden p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Journal d'erreurs Laravel</p>
                        <p class="text-xs text-gray-400 mt-0.5" id="log-size-label"></p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="loadLogs()"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Rafraîchir
                        </button>
                        <button onclick="confirmClearLogs()"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Vider les logs
                        </button>
                    </div>
                </div>
                <div id="log-loading" class="hidden text-center py-8 text-gray-400 text-sm">Chargement...</div>
                <pre id="log-content"
                     class="bg-gray-900 text-gray-100 rounded-lg p-4 text-[11px] font-mono leading-relaxed overflow-auto max-h-[500px] whitespace-pre-wrap break-all">
<span class="text-gray-500 italic">Cliquez sur "Rafraîchir" pour charger les logs.</span></pre>
            </div>
        </div>

        {{-- ── Tab Twilio Flow API ──────────────────────────── --}}
        @php $baseUrl = rtrim(config('app.url'), '/'); @endphp
        <div id="panel-twilio-flow" class="tab-panel hidden p-6 space-y-6">

            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Documentation des endpoints Twilio Flow</p>
                    <p class="text-xs text-gray-500 mt-0.5">Intégrez ces endpoints dans vos widgets HTTP de Twilio Studio.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 border border-green-200 text-green-700 text-xs font-medium rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    Pas d'authentification requise
                </span>
            </div>

            {{-- URL de base --}}
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-medium text-indigo-600 uppercase tracking-wide mb-1">URL de base</p>
                    <code class="text-sm font-mono text-indigo-900 select-all" id="base-url-text">{{ $baseUrl }}</code>
                </div>
                <button onclick="copyText('{{ $baseUrl }}', this)" class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-600 bg-white border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Copier
                </button>
            </div>

            {{-- ───────── Endpoint 1 ───────── --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-600 text-white uppercase tracking-wide">POST</span>
                    <code class="text-sm font-mono text-gray-800 flex-1 select-all" id="url-incoming">{{ $baseUrl }}/bot-tracking/twilio/incoming</code>
                    <button onclick="copyText('{{ $baseUrl }}/bot-tracking/twilio/incoming', this)" class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Copier
                    </button>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-700 mb-1">Description</p>
                        <p class="text-sm text-gray-600">Point d'entrée principal. À appeler en premier à chaque message WhatsApp reçu. Crée ou retrouve la conversation active et retourne l'état du client.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-700 mb-2">Paramètres envoyés <span class="text-gray-400 font-normal">(form-urlencoded)</span></p>
                            <div class="space-y-1.5">
                                @foreach([
                                    ['From','required','Numéro WhatsApp expéditeur (ex: whatsapp:+212XXXXXX)'],
                                    ['Body','optional','Corps du message reçu'],
                                    ['MessageSid','required','Identifiant unique du message Twilio'],
                                    ['ProfileName','optional','Nom de profil WhatsApp de l\'utilisateur'],
                                    ['NumMedia','optional','Nombre de médias joints (0 par défaut)'],
                                ] as [$param, $req, $desc])
                                <div class="flex items-start gap-2 text-xs">
                                    <code class="font-mono text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded whitespace-nowrap">{{ $param }}</code>
                                    <span class="px-1 py-0.5 rounded text-[10px] font-medium {{ $req === 'required' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500' }} whitespace-nowrap">{{ $req === 'required' ? 'requis' : 'optionnel' }}</span>
                                    <span class="text-gray-500">{{ $desc }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-700 mb-2">Réponse JSON</p>
                            <div class="space-y-1.5">
                                @foreach([
                                    ['conversation_id','ID interne de la conversation'],
                                    ['current_menu','Menu actuel du bot'],
                                    ['is_client','true/false — Client SportCash ?'],
                                    ['client_full_name','Nom complet du client (ou null)'],
                                    ['agent_mode','"true" si transféré à un agent'],
                                    ['pending_agent','"true" si en attente d\'agent'],
                                    ['client_exists','"true" si le client existe en BDD'],
                                    ['client_has_name','"true" si un nom est enregistré'],
                                    ['client_status_known','"true" si is_client est défini'],
                                ] as [$field, $desc])
                                <div class="flex items-start gap-2 text-xs">
                                    <code class="font-mono text-green-700 bg-green-50 px-1.5 py-0.5 rounded whitespace-nowrap">{{ $field }}</code>
                                    <span class="text-gray-500">{{ $desc }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-lg p-3">
                        <p class="text-xs font-semibold text-amber-700 mb-1">Configuration dans Twilio Studio (Widget HTTP)</p>
                        <div class="space-y-1 text-xs text-amber-800 font-mono">
                            <p><span class="text-amber-500">Request Method:</span> POST</p>
                            <p><span class="text-amber-500">Request URL:</span> {{ $baseUrl }}/bot-tracking/twilio/incoming</p>
                            <p><span class="text-amber-500">Parameters:</span> From=@{{trigger.message.From}}, Body=@{{trigger.message.Body}}, MessageSid=@{{trigger.message.MessageSid}}, ProfileName=@{{trigger.message.ProfileName}}</p>
                            <p class="text-amber-600 mt-1">→ Variables dispo : @{{widgets.NOM_WIDGET.parsed.conversation_id}}, @{{widgets.NOM_WIDGET.parsed.is_client}}, etc.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ───────── Endpoint 2 ───────── --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-600 text-white uppercase tracking-wide">POST</span>
                    <code class="text-sm font-mono text-gray-800 flex-1 select-all">{{ $baseUrl }}/bot-tracking/twilio/menu-choice</code>
                    <button onclick="copyText('{{ $baseUrl }}/bot-tracking/twilio/menu-choice', this)" class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Copier
                    </button>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-700 mb-1">Description</p>
                        <p class="text-sm text-gray-600">Enregistre le choix de menu fait par l'utilisateur. Met à jour le menu courant et historise le parcours. À appeler après chaque branche de menu.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-700 mb-2">Paramètres envoyés</p>
                            <div class="space-y-1.5">
                                @foreach([
                                    ['conversation_id','required','ID retourné par l\'endpoint incoming'],
                                    ['menu_choice','required','Identifiant du menu (ex: menu_sav, vehicules_neufs...)'],
                                    ['user_input','optional','Texte brut saisi par l\'utilisateur'],
                                ] as [$param, $req, $desc])
                                <div class="flex items-start gap-2 text-xs">
                                    <code class="font-mono text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded whitespace-nowrap">{{ $param }}</code>
                                    <span class="px-1 py-0.5 rounded text-[10px] font-medium {{ $req === 'required' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500' }} whitespace-nowrap">{{ $req === 'required' ? 'requis' : 'optionnel' }}</span>
                                    <span class="text-gray-500">{{ $desc }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-700 mb-2">Réponse JSON</p>
                            <div class="space-y-1.5">
                                @foreach([
                                    ['success','true si enregistré avec succès'],
                                    ['current_menu','Menu courant mis à jour'],
                                    ['menu_path','Tableau du parcours complet'],
                                ] as [$field, $desc])
                                <div class="flex items-start gap-2 text-xs">
                                    <code class="font-mono text-green-700 bg-green-50 px-1.5 py-0.5 rounded whitespace-nowrap">{{ $field }}</code>
                                    <span class="text-gray-500">{{ $desc }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-lg p-3">
                        <p class="text-xs font-semibold text-amber-700 mb-1">Exemple de configuration Twilio Studio</p>
                        <div class="space-y-1 text-xs text-amber-800 font-mono">
                            <p><span class="text-amber-500">Parameters:</span> conversation_id=@{{widgets.init_tracking.parsed.conversation_id}}, menu_choice=menu_sav, user_input=@{{trigger.message.Body}}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ───────── Endpoint 3 ───────── --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-600 text-white uppercase tracking-wide">POST</span>
                    <code class="text-sm font-mono text-gray-800 flex-1 select-all">{{ $baseUrl }}/bot-tracking/twilio/free-input</code>
                    <button onclick="copyText('{{ $baseUrl }}/bot-tracking/twilio/free-input', this)" class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Copier
                    </button>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-700 mb-1">Description</p>
                        <p class="text-sm text-gray-600">Enregistre une saisie libre de l'utilisateur et met à jour automatiquement la fiche client selon le <code class="text-xs bg-gray-100 px-1 rounded">widget_name</code> transmis.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-700 mb-2">Paramètres envoyés</p>
                            <div class="space-y-1.5">
                                @foreach([
                                    ['conversation_id','required','ID de la conversation'],
                                    ['user_input','required','Texte saisi par l\'utilisateur'],
                                    ['widget_name','optional','Contexte de la saisie (voir tableau ci-dessous)'],
                                ] as [$param, $req, $desc])
                                <div class="flex items-start gap-2 text-xs">
                                    <code class="font-mono text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded whitespace-nowrap">{{ $param }}</code>
                                    <span class="px-1 py-0.5 rounded text-[10px] font-medium {{ $req === 'required' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500' }} whitespace-nowrap">{{ $req === 'required' ? 'requis' : 'optionnel' }}</span>
                                    <span class="text-gray-500">{{ $desc }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-700 mb-2">Valeurs de <code class="text-xs bg-gray-100 px-1 rounded">widget_name</code> reconnues</p>
                            <div class="space-y-1.5">
                                @foreach([
                                    ['collect_name','Enregistre le nom complet du client'],
                                    ['collect_email','Enregistre l\'adresse email'],
                                    ['collect_vin','Enregistre le numéro VIN du véhicule'],
                                    ['collect_carte_vip','Enregistre le numéro carte VIP'],
                                    ['check_client','Définit is_client (1/oui → true)'],
                                ] as [$val, $desc])
                                <div class="flex items-start gap-2 text-xs">
                                    <code class="font-mono text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded whitespace-nowrap">{{ $val }}</code>
                                    <span class="text-gray-500">{{ $desc }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-lg p-3">
                        <p class="text-xs font-semibold text-amber-700 mb-1">Exemple — Collecte du nom</p>
                        <div class="text-xs text-amber-800 font-mono">
                            <p><span class="text-amber-500">Parameters:</span> conversation_id=@{{widgets.init_tracking.parsed.conversation_id}}, user_input=@{{widgets.ask_name.inbound.Body}}, widget_name=collect_name</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ───────── Endpoint 4 ───────── --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-600 text-white uppercase tracking-wide">POST</span>
                    <code class="text-sm font-mono text-gray-800 flex-1 select-all">{{ $baseUrl }}/bot-tracking/twilio/complete</code>
                    <button onclick="copyText('{{ $baseUrl }}/bot-tracking/twilio/complete', this)" class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Copier
                    </button>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-700 mb-1">Description</p>
                        <p class="text-sm text-gray-600">Clôture la conversation, calcule la durée totale et passe le statut à <code class="text-xs bg-gray-100 px-1 rounded">completed</code>. À appeler en fin de flow (widget "End Flow").</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-700 mb-2">Paramètres envoyés</p>
                            <div class="flex items-start gap-2 text-xs">
                                <code class="font-mono text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded">conversation_id</code>
                                <span class="px-1 py-0.5 rounded text-[10px] font-medium bg-red-50 text-red-600">requis</span>
                                <span class="text-gray-500">ID de la conversation à clôturer</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-700 mb-2">Réponse JSON</p>
                            <div class="space-y-1.5">
                                @foreach([
                                    ['success','true si clôturé avec succès'],
                                    ['completed','true'],
                                    ['duration_seconds','Durée totale de la conversation en secondes'],
                                ] as [$field, $desc])
                                <div class="flex items-start gap-2 text-xs">
                                    <code class="font-mono text-green-700 bg-green-50 px-1.5 py-0.5 rounded whitespace-nowrap">{{ $field }}</code>
                                    <span class="text-gray-500">{{ $desc }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-lg p-3">
                        <p class="text-xs font-semibold text-amber-700 mb-1">Exemple de configuration Twilio Studio</p>
                        <div class="text-xs text-amber-800 font-mono">
                            <p><span class="text-amber-500">Parameters:</span> conversation_id=@{{widgets.init_tracking.parsed.conversation_id}}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Schéma du flow recommandé --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <p class="text-sm font-semibold text-gray-900 mb-4">Schéma d'intégration recommandé dans Twilio Studio</p>
                <div class="overflow-x-auto">
                    <div class="flex items-start gap-2 min-w-max text-xs">
                        @foreach([
                            ['trigger','Trigger\n(Message reçu)','bg-gray-100 text-gray-700 border-gray-200'],
                            ['→','',''],
                            ['init','HTTP\ninit_tracking\n→ /twilio/incoming','bg-blue-50 text-blue-700 border-blue-200'],
                            ['→','',''],
                            ['split','Split\nagent_mode = "true" ?','bg-yellow-50 text-yellow-700 border-yellow-200'],
                            ['→','',''],
                            ['menu','HTTP\ntrack_menu\n→ /twilio/menu-choice','bg-indigo-50 text-indigo-700 border-indigo-200'],
                            ['→','',''],
                            ['input','HTTP\ntrack_input\n→ /twilio/free-input','bg-purple-50 text-purple-700 border-purple-200'],
                            ['→','',''],
                            ['complete','HTTP\ncomplete_conv\n→ /twilio/complete','bg-green-50 text-green-700 border-green-200'],
                        ] as [$key, $label, $style])
                            @if($key === '→')
                                <div class="flex items-center self-center pt-0"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                            @else
                                <div class="border rounded-lg px-3 py-2 text-center font-mono whitespace-pre-line leading-snug {{ $style }}">{{ $label }}</div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-3">Note : le widget <strong class="text-gray-600">init_tracking</strong> est appelé en premier. Son <code class="bg-gray-100 px-1 rounded">conversation_id</code> est passé en paramètre à tous les widgets suivants.</p>
            </div>

        </div>{{-- /panel-twilio-flow --}}

    </div>
</div>
@endsection

@push('scripts')
<script>
const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

// ═══ Onglets ═══════════════════════════════════════════
function switchTab(name) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('active-tab', 'border-indigo-600', 'text-indigo-600');
        b.classList.add('border-transparent', 'text-gray-500');
    });

    document.getElementById('panel-' + name).classList.remove('hidden');
    const btn = document.getElementById('tab-' + name);
    btn.classList.add('active-tab', 'border-indigo-600', 'text-indigo-600');
    btn.classList.remove('border-transparent', 'text-gray-500');

    if (name === 'logs') loadLogs();
}

// ═══ Tester les connexions ══════════════════════════════
async function testConnection(service) {
    const statusEl  = document.getElementById('status-' + service);
    const detailEl  = document.getElementById('detail-' + service);

    // État "en cours"
    statusEl.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Test...`;
    statusEl.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-500';

    try {
        const r    = await fetch(`/ajax/settings/test-${service}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
        });
        const data = await r.json();

        if (data.ok) {
            statusEl.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> OK`;
            statusEl.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-green-50 text-green-700';
            detailEl.textContent = `Connexion établie — ${data.latency_ms}ms`;
            detailEl.className = 'text-xs text-green-600 min-h-[16px]';
        } else {
            statusEl.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Erreur`;
            statusEl.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-red-50 text-red-700';
            detailEl.textContent = data.error || 'Erreur inconnue';
            detailEl.className = 'text-xs text-red-500 min-h-[16px]';
        }
    } catch (e) {
        statusEl.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Erreur`;
        statusEl.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-red-50 text-red-700';
        detailEl.textContent = 'Erreur réseau';
        detailEl.className = 'text-xs text-red-500 min-h-[16px]';
    }
}

async function testAll() {
    const btn = document.getElementById('btn-test-all');
    btn.disabled = true;
    btn.classList.add('opacity-60');
    await Promise.all([
        testConnection('chatwoot'),
        testConnection('twilio'),
        testConnection('database'),
    ]);
    btn.disabled = false;
    btn.classList.remove('opacity-60');
}

// ═══ Enregistrer une section ════════════════════════════
async function saveSection(e, section) {
    e.preventDefault();
    const form   = e.target;
    const btn    = form.querySelector('button[type="submit"]');
    const label  = btn.querySelector('.save-label');
    const icon   = btn.querySelector('.save-icon');

    btn.disabled = true;
    label.textContent = 'Enregistrement...';

    const data = {};
    new FormData(form).forEach((v, k) => { if (k !== '_token') data[k] = v; });

    try {
        const r = await fetch('/ajax/settings/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        });
        const res = await r.json();

        if (res.ok) {
            label.textContent = 'Enregistré !';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
            btn.classList.replace('bg-indigo-600', 'bg-green-600');
            showToast('Configuration enregistrée avec succès', 'success');
        } else {
            label.textContent = 'Erreur';
            showToast('Erreur : ' + (res.error || 'Inconnue'), 'error');
        }
    } catch (ex) {
        label.textContent = 'Erreur';
        showToast('Erreur réseau', 'error');
    }

    setTimeout(() => {
        btn.disabled = false;
        label.textContent = 'Enregistrer';
        btn.classList.replace('bg-green-600', 'bg-indigo-600');
    }, 2000);
}

// ═══ Toggle reveal password ═════════════════════════════
function toggleReveal(fieldId) {
    const input = document.getElementById(fieldId);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// ═══ Toggle debug ═══════════════════════════════════════
function toggleDebug(btn) {
    const isOn   = btn.dataset.on === 'true';
    const newVal = !isOn;
    btn.dataset.on = String(newVal);
    document.getElementById('app-debug-value').value = String(newVal);
    const label = document.getElementById('debug-label');

    if (newVal) {
        btn.classList.replace('bg-gray-300', 'bg-red-500');
        btn.querySelector('span').classList.replace('translate-x-0.5', 'translate-x-5');
        label.textContent = 'Activé (attention !)';
        label.className = 'text-xs text-red-500 font-medium';
    } else {
        btn.classList.replace('bg-red-500', 'bg-gray-300');
        btn.querySelector('span').classList.replace('translate-x-5', 'translate-x-0.5');
        label.textContent = 'Désactivé';
        label.className = 'text-xs text-gray-400';
    }
}

// ═══ Logs ═══════════════════════════════════════════════
async function loadLogs() {
    const pre     = document.getElementById('log-content');
    const loading = document.getElementById('log-loading');
    const sizeEl  = document.getElementById('log-size-label');

    pre.classList.add('opacity-40');
    loading.classList.remove('hidden');

    try {
        const r    = await fetch('/ajax/settings/logs?lines=200', {
            headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' }
        });
        const data = await r.json();
        const lines = data.lines || [];
        const sizeKb = ((data.size || 0) / 1024).toFixed(1);

        sizeEl.textContent = `${lines.length} lignes — ${sizeKb} Ko`;

        if (lines.length === 0) {
            pre.textContent = 'Aucune entrée dans les logs.';
        } else {
            pre.textContent = lines.join('\n');
            pre.scrollTop = pre.scrollHeight;
        }
    } catch (e) {
        pre.textContent = 'Erreur lors du chargement des logs.';
    }

    loading.classList.add('hidden');
    pre.classList.remove('opacity-40');
}

async function confirmClearLogs() {
    if (!confirm('Vider tous les logs ? Cette action est irréversible.')) return;

    try {
        const r = await fetch('/ajax/settings/logs', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
        });
        const data = await r.json();

        if (data.ok) {
            document.getElementById('log-content').textContent = 'Logs vidés.';
            document.getElementById('log-size-label').textContent = '0 lignes — 0 Ko';
            showToast('Logs vidés avec succès', 'success');
        }
    } catch (e) {
        showToast('Erreur lors de la suppression des logs', 'error');
    }
}

// ═══ Toast ══════════════════════════════════════════════
function showToast(message, type = 'success') {
    const container = document.getElementById('msg-toasts');
    if (!container) return;

    const toast = document.createElement('div');
    const colors = type === 'success'
        ? 'bg-green-50 border-green-200 text-green-800'
        : 'bg-red-50 border-red-200 text-red-800';

    toast.className = `${colors} border rounded-xl shadow-lg px-4 py-3 flex items-center gap-3 text-sm font-medium animate-slide-in`;
    toast.innerHTML = `
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${type === 'success'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>'}
        </svg>
        ${escH(message)}`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 4000);
}

function escH(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

// ═══ Copy to clipboard ══════════════════════════════
async function copyText(text, btn) {
    try {
        await navigator.clipboard.writeText(text);
        const orig = btn.innerHTML;
        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copié !';
        btn.classList.add('text-green-600', 'border-green-300', 'bg-green-50');
        setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('text-green-600', 'border-green-300', 'bg-green-50'); }, 2000);
    } catch {
        showToast('Impossible de copier dans le presse-papier', 'error');
    }
}
</script>
@endpush
