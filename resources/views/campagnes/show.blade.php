@extends('layouts.app')
@section('title', $campaign->name)

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-6">

        {{-- HEADER --}}
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('campagnes.index') }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl font-bold text-gray-900">{{ $campaign->name }}</h1>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $campaign->statusBadgeClass() }}">{{ $campaign->statusLabel() }}</span>
                        @if($campaign->hasPendingSchedule())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700 border border-amber-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Planification en cours · {{ $campaign->scheduled_at->format('d/m H:i') }}
                        </span>
                        @endif
                    </div>
                    @if($campaign->description)
                    <p class="text-sm text-gray-500 mt-0.5">{{ $campaign->description }}</p>
                    @endif
                    <p class="text-[10px] text-gray-400 mt-1">{{ $campaign->created_at->format('d/m/Y H:i') }}{{ $campaign->creator ? ' · ' . $campaign->creator->name : '' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('campagnes.edit', $campaign) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Modifier
                </a>
                <button onclick="confirmDelete()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-red-500 bg-white border border-gray-200 rounded-lg hover:bg-red-50 hover:border-red-200 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Supprimer
                </button>
            </div>
        </div>

        {{-- LAYOUT 2 COLONNES --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- COLONNE GAUCHE (2/3) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- CONTACTS CIBLES --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <h2 class="text-sm font-semibold text-gray-900">Contacts cibles</h2>
                            <span class="px-1.5 py-0.5 bg-primary-50 text-primary-700 rounded text-[10px] font-bold">{{ $totalTargetContacts }}</span>
                        </div>
                    </div>

                    {{-- Recherche contacts cibles (AJAX) --}}
                    <div class="px-5 py-3 border-b border-gray-50">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="search-targets" placeholder="Rechercher dans les contacts cibles..."
                                   autocomplete="off"
                                   class="w-full pl-9 pr-10 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            <svg id="targets-spinner" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary-400 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </div>
                    </div>

                    {{-- Liste contacts cibles (rendue dynamiquement) --}}
                    <div id="target-contacts-list"></div>

                    {{-- Pagination AJAX --}}
                    <div id="target-contacts-pagination" class="hidden px-5 py-3 border-t border-gray-100 bg-gray-50/50"></div>

                    {{-- Empty state --}}
                    <div id="target-contacts-empty" class="hidden px-5 py-10 text-center">
                        <svg class="w-8 h-8 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <p class="text-sm text-gray-400" id="target-empty-text">Aucun contact cible</p>
                        <p class="text-[10px] text-gray-300 mt-0.5">Recherchez et ajoutez des contacts depuis le panneau a droite</p>
                    </div>
                </div>

                {{-- HISTORIQUE ENVOIS --}}
                @if($campaign->messages->count())
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <h2 class="text-sm font-semibold text-gray-900">Historique</h2>
                            <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">{{ $campaign->messages->count() }}</span>
                        </div>
                        <button onclick="refreshStatuses(this)" class="inline-flex items-center gap-1.5 text-[11px] font-medium text-gray-500 hover:text-primary-600 transition">
                            <svg class="w-3.5 h-3.5 refresh-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Rafraichir
                        </button>
                    </div>
                    <div class="max-h-[320px] overflow-y-auto scrollbar-thin">
                        @foreach($campaign->messages as $msg)
                        <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-[9px] font-bold flex-shrink-0">{{ $msg->contact ? $msg->contact->initials() : '?' }}</div>
                                <span class="text-sm text-gray-800 truncate">{{ $msg->contact->name ?? 'Inconnu' }}</span>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                @if($msg->error_message)
                                <span class="text-[10px] text-red-500 max-w-[120px] truncate hidden sm:block" title="{{ $msg->error_message }}">{{ Str::limit($msg->error_message, 25) }}</span>
                                @endif
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $msg->statusBadgeClass() }}">{{ $msg->statusLabel() }}</span>
                                <span class="text-[10px] text-gray-400 w-16 text-right">{{ $msg->sent_at ? $msg->sent_at->format('H:i') : '-' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- COLONNE DROITE (1/3) --}}
            <div class="space-y-5">

                {{-- PLANIFICATION EN COURS --}}
                @if($campaign->hasPendingSchedule())
                <div class="bg-purple-50 rounded-xl border border-purple-200 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h3 class="text-sm font-semibold text-purple-900">Envoi planifie</h3>
                    </div>
                    <div class="bg-white rounded-lg px-4 py-3 border border-purple-100 mb-3">
                        <p class="text-[10px] text-purple-500 uppercase font-semibold mb-0.5">Date prevue</p>
                        <p class="text-sm font-bold text-purple-900">{{ $campaign->scheduled_at->format('d/m/Y \a H:i') }}</p>
                        <p class="text-xs text-purple-600 mt-0.5">{{ $campaign->scheduled_at->diffForHumans() }}</p>
                    </div>
                    <p class="text-xs text-purple-700 mb-3">Le template <strong>{{ $campaign->template_name }}</strong> sera envoyé à <strong>{{ $totalTargetContacts }} contact(s)</strong>.</p>
                    <button onclick="doCancelSchedule(this)" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-white text-purple-700 text-xs font-medium rounded-lg border border-purple-200 hover:bg-purple-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Annuler la planification
                    </button>
                </div>
                @endif

                {{-- ACTIONS --}}
                @if(in_array($campaign->status, ['draft', 'scheduled']))
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                    <h3 class="text-sm font-semibold text-gray-900">Actions</h3>
                    @if($campaign->status === 'draft')
                    <button onclick="openSendModal()" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-500 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Envoyer maintenant
                    </button>
                    <button onclick="openScheduleModal()" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Planifier
                    </button>
                    @endif
                </div>
                @endif

                {{-- RÉOUVRIR CAMPAGNE TERMINÉE --}}
                @if($campaign->status === 'completed')
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                    <h3 class="text-sm font-semibold text-gray-900">Actions</h3>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5 text-xs text-amber-700 leading-relaxed">
                        Cette campagne est terminée. Réouvrez-la pour modifier le template, ajouter des contacts ou lancer un nouvel envoi.
                    </div>
                    <button onclick="doReopen(this)" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 transition shadow-sm">
                        <svg class="w-4 h-4 spinner hidden animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <svg class="w-4 h-4 reopen-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span class="btn-label">Réouvrir la campagne</span>
                    </button>
                </div>
                @endif

                {{-- DELIVRABILITE --}}
                @if(($stats['total'] ?? 0) > 0)
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Delivrabilite</h3>
                        <div class="flex gap-0.5 bg-gray-100 rounded-lg p-0.5" id="stats-period-selector">
                            @foreach(['all' => 'Tout', 'today' => 'Jour', 'week' => 'Sem.', 'month' => 'Mois'] as $p => $label)
                            <button onclick="changeStatsPeriod('{{ $p }}')" data-period="{{ $p }}"
                                    class="stats-period-btn px-2 py-1 text-[10px] font-medium rounded-md transition {{ $p === 'all' ? 'bg-white shadow-sm text-primary-700' : 'text-gray-500 hover:text-gray-700' }}">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @php
                        $total = max($stats['total'], 1);
                        $bars = [
                            ['label' => 'Delivres', 'key' => 'delivered', 'count' => $stats['delivered'] ?? 0, 'color' => 'bg-green-500'],
                            ['label' => 'Lus',      'key' => 'read',      'count' => $stats['read'] ?? 0,      'color' => 'bg-indigo-500'],
                            ['label' => 'Envoyes',  'key' => 'sent',      'count' => $stats['sent'] ?? 0,      'color' => 'bg-blue-400'],
                            ['label' => 'En file',  'key' => 'queued',    'count' => $stats['queued'] ?? 0,    'color' => 'bg-gray-300'],
                            ['label' => 'Echoues',  'key' => 'failed',    'count' => $stats['failed'] ?? 0,    'color' => 'bg-red-400'],
                        ];
                    @endphp
                    <div class="space-y-2.5" id="stats-bars">
                        @foreach($bars as $bar)
                        @if($bar['count'] > 0)
                        <div data-stat="{{ $bar['key'] }}">
                            <div class="flex items-center justify-between text-[11px] mb-1">
                                <span class="text-gray-600">{{ $bar['label'] }}</span>
                                <span class="font-semibold text-gray-800"><span class="stat-count">{{ $bar['count'] }}</span><span class="text-gray-400 font-normal">/<span class="stat-total">{{ $stats['total'] }}</span></span></span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="{{ $bar['color'] }} h-full rounded-full stat-bar" style="width: {{ round($bar['count'] / $total * 100) }}%"></div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- TEMPLATE --}}
                @if($campaign->template_body)
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Template</h3>
                    <div class="bg-primary-50 rounded-lg p-3.5">
                        <p class="text-[11px] font-semibold text-primary-700 mb-1">{{ $campaign->template_name }}</p>
                        <p class="text-xs text-primary-900/80 whitespace-pre-wrap leading-relaxed">{{ $campaign->template_body }}</p>
                    </div>
                </div>
                @endif

                {{-- AJOUTER DES CONTACTS (AUTOCOMPLETION) --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2.5">Ajouter des contacts</h3>
                        <div class="relative" id="autocomplete-wrapper">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="search-available" placeholder="Rechercher par nom ou telephone..."
                                   autocomplete="off"
                                   class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            <svg id="search-spinner" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary-400 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>

                            {{-- Dropdown autocompletion --}}
                            <div id="autocomplete-dropdown" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 max-h-[280px] overflow-y-auto">
                                {{-- Resultats injectes dynamiquement --}}
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-3 text-center">
                        <a href="{{ route('campagnes.contacts.index') }}" class="text-[11px] text-primary-600 hover:underline">Gerer les contacts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALE ENVOI --}}
<div id="send-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-[420px] max-w-[90vw] overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 text-sm">Confirmer l'envoi</h3>
            <button onclick="closeSendModal()" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="px-5 py-5">
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <div>
                        <p class="text-sm font-medium text-amber-800">Envoi immediat</p>
                        <p class="text-xs text-amber-700 mt-1">Le template <strong>{{ $campaign->template_name }}</strong> sera envoye a <strong>{{ $totalTargetContacts }} contact(s)</strong>.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <button onclick="closeSendModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">Annuler</button>
            <button onclick="confirmSend(this)" class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 transition shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4 spinner hidden animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span class="btn-label">Confirmer l'envoi</span>
            </button>
        </div>
    </div>
</div>

{{-- MODALE PLANIFICATION --}}
<div id="schedule-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-[420px] max-w-[90vw] overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 text-sm">Planifier l'envoi</h3>
            <button onclick="closeScheduleModal()" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="px-5 py-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Date et heure</label>
                <input type="datetime-local" id="schedule-datetime" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
            </div>
            <p class="text-xs text-gray-500">Le template sera envoye a <strong>{{ $totalTargetContacts }} contact(s)</strong> a la date choisie.</p>
        </div>
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <button onclick="closeScheduleModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">Annuler</button>
            <button onclick="confirmSchedule(this)" class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 transition shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4 spinner hidden animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span class="btn-label">Planifier</span>
            </button>
        </div>
    </div>
</div>

{{-- MODALE CONFIRMATION SUPPRESSION --}}
<div id="delete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-[400px] max-w-[90vw] overflow-hidden">
        <div class="px-5 py-5 text-center">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Supprimer cette campagne ?</h3>
            <p class="text-sm text-gray-500">Tous les messages et contacts associes seront supprimes. Cette action est irreversible.</p>
        </div>
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">Annuler</button>
            <button onclick="doDelete(this)" class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition inline-flex items-center gap-2">
                <svg class="w-4 h-4 spinner hidden animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span class="btn-label">Supprimer</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' };

    // ═══ TOAST SYSTEM ═══
    function toast(message, type = 'success') {
        const container = document.getElementById('msg-toasts');
        if (!container) return;
        const colors = {
            success: 'border-green-200 bg-green-50',
            error: 'border-red-200 bg-red-50',
            info: 'border-blue-200 bg-blue-50',
        };
        const icons = {
            success: '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            error: '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            info: '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        };
        const el = document.createElement('div');
        el.className = `border ${colors[type]} rounded-xl shadow-2xl p-3.5 flex items-start gap-3 animate-slide-in`;
        el.innerHTML = `${icons[type]}<p class="text-sm text-gray-800 font-medium">${message}</p>
            <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600 flex-shrink-0 ml-auto"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>`;
        container.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateX(100%)'; el.style.transition = 'all 0.3s'; setTimeout(() => el.remove(), 300); }, 5000);
    }

    // ═══ LOADER HELPERS ═══
    function btnLoad(btn) { btn.disabled = true; btn.querySelector('.spinner')?.classList.remove('hidden'); }
    function btnReset(btn) { btn.disabled = false; btn.querySelector('.spinner')?.classList.add('hidden'); }

    // ═══ STATS PAR PERIODE ═══
    let currentStatsPeriod = 'all';
    const barsConfig = [
        { key: 'delivered', label: 'Delivres', color: 'bg-green-500' },
        { key: 'read',      label: 'Lus',      color: 'bg-indigo-500' },
        { key: 'sent',      label: 'Envoyes',  color: 'bg-blue-400' },
        { key: 'queued',    label: 'En file',  color: 'bg-gray-300' },
        { key: 'failed',    label: 'Echoues',  color: 'bg-red-400' },
    ];

    window.changeStatsPeriod = async function(period) {
        if (period === currentStatsPeriod) return;
        currentStatsPeriod = period;
        document.querySelectorAll('.stats-period-btn').forEach(b => {
            b.classList.toggle('bg-white', b.dataset.period === period);
            b.classList.toggle('shadow-sm', b.dataset.period === period);
            b.classList.toggle('text-primary-700', b.dataset.period === period);
            b.classList.toggle('text-gray-500', b.dataset.period !== period);
        });

        try {
            const r = await fetch(`{{ route('ajax.campagnes.periodStats', $campaign) }}?period=${period}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await r.json();
            const container = document.getElementById('stats-bars');
            const total = Math.max(data.total || 0, 1);

            // Rebuild bars
            container.innerHTML = '';
            barsConfig.forEach(bar => {
                const count = data[bar.key] || 0;
                if (count > 0) {
                    const pct = Math.round(count / total * 100);
                    container.innerHTML += `<div data-stat="${bar.key}">
                        <div class="flex items-center justify-between text-[11px] mb-1">
                            <span class="text-gray-600">${bar.label}</span>
                            <span class="font-semibold text-gray-800">${count}<span class="text-gray-400 font-normal">/${data.total}</span></span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="${bar.color} h-full rounded-full" style="width: ${pct}%"></div>
                        </div>
                    </div>`;
                }
            });
            if (data.total === 0) {
                container.innerHTML = '<p class="text-xs text-gray-400 text-center py-2">Aucune donnee pour cette periode</p>';
            }
        } catch(e) { toast('Erreur chargement stats', 'error'); }
    };

    // ═══ AUTOCOMPLETION CONTACTS DISPONIBLES ═══
    const searchInput = document.getElementById('search-available');
    const dropdown = document.getElementById('autocomplete-dropdown');
    const spinner = document.getElementById('search-spinner');
    let searchTimeout = null;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.trim();
            clearTimeout(searchTimeout);

            if (q.length < 1) {
                dropdown.classList.add('hidden');
                dropdown.innerHTML = '';
                return;
            }

            spinner.classList.remove('hidden');
            searchTimeout = setTimeout(async () => {
                try {
                    const r = await fetch(`{{ route('ajax.campagnes.availableContacts', $campaign) }}?q=${encodeURIComponent(q)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const contacts = await r.json();
                    renderDropdown(contacts, q);
                } catch(e) {
                    dropdown.innerHTML = '<div class="px-4 py-3 text-xs text-red-500">Erreur de recherche</div>';
                    dropdown.classList.remove('hidden');
                }
                spinner.classList.add('hidden');
            }, 300);
        });

        // Fermer le dropdown en cliquant ailleurs
        document.addEventListener('click', function(e) {
            if (!document.getElementById('autocomplete-wrapper').contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    function renderDropdown(contacts, query) {
        if (!contacts.length) {
            dropdown.innerHTML = '<div class="px-4 py-3 text-xs text-gray-400 text-center">Aucun contact disponible</div>';
            dropdown.classList.remove('hidden');
            return;
        }

        dropdown.innerHTML = contacts.map(c => {
            const nameHtml = highlightMatch(c.name, query);
            const phoneHtml = highlightMatch(c.phone_number, query);
            const initials = c.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
            return `<div class="flex items-center justify-between px-4 py-2.5 hover:bg-primary-50/50 transition cursor-pointer border-b border-gray-50 last:border-0" data-id="${c.id}">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0">${initials}</div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-800 truncate">${nameHtml}</p>
                        <p class="text-[10px] text-gray-400 font-mono">${phoneHtml}</p>
                    </div>
                </div>
                <button onclick="event.stopPropagation(); addFromDropdown(${c.id}, this)" class="flex-shrink-0 px-2 py-1 text-[10px] font-medium text-primary-600 bg-primary-50 rounded-md hover:bg-primary-100 transition">
                    + Ajouter
                </button>
            </div>`;
        }).join('');

        dropdown.classList.remove('hidden');
    }

    function highlightMatch(text, query) {
        if (!query || !text) return text || '';
        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark class="bg-yellow-200 rounded px-0.5">$1</mark>');
    }

    window.addFromDropdown = async function(id, btn) {
        btn.disabled = true;
        btn.textContent = '...';
        try {
            const r = await fetch('{{ route("ajax.campagnes.attachContacts", $campaign) }}', { method: 'POST', headers, body: JSON.stringify({ contact_ids: [id] }) });
            const res = await r.json();
            if (res.success) {
                toast('Contact ajoute');
                btn.closest('[data-id]')?.remove();
                if (!dropdown.querySelector('[data-id]')) dropdown.classList.add('hidden');
                loadTargetContacts(1, targetCurrentQuery);
            } else {
                toast(res.message || 'Erreur', 'error');
                btn.disabled = false;
                btn.textContent = '+ Ajouter';
            }
        } catch(e) {
            toast('Erreur reseau', 'error');
            btn.disabled = false;
            btn.textContent = '+ Ajouter';
        }
    };

    // ═══ CONTACTS CIBLES (AJAX avec recherche + pagination) ═══
    const targetList = document.getElementById('target-contacts-list');
    const targetPagination = document.getElementById('target-contacts-pagination');
    const targetEmpty = document.getElementById('target-contacts-empty');
    const targetSpinner = document.getElementById('targets-spinner');
    const searchTargets = document.getElementById('search-targets');
    let targetSearchTimeout = null;
    let targetCurrentPage = 1;
    let targetCurrentQuery = '';
    const canSend = {{ in_array($campaign->status, ['draft', 'active', 'scheduled']) ? 'true' : 'false' }};

    function loadTargetContacts(page = 1, q = '') {
        targetCurrentPage = page;
        targetCurrentQuery = q;
        targetSpinner?.classList.remove('hidden');

        const params = new URLSearchParams({ page });
        if (q) params.set('q', q);

        fetch(`{{ route('ajax.campagnes.listContacts', $campaign) }}?${params}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            renderTargetContacts(data);
        })
        .catch(() => {
            targetList.innerHTML = '<div class="px-5 py-4 text-center text-xs text-red-500">Erreur de chargement</div>';
        })
        .finally(() => targetSpinner?.classList.add('hidden'));
    }

    function renderTargetContacts(data) {
        const contacts = data.data || [];
        const totalCount = data.total || 0;

        // Mise a jour du badge total
        const badge = document.querySelector('.bg-primary-50.text-primary-700.rounded');
        if (badge) badge.textContent = totalCount;

        if (contacts.length === 0) {
            targetList.innerHTML = '';
            targetPagination.classList.add('hidden');
            targetEmpty.classList.remove('hidden');
            targetEmpty.querySelector('#target-empty-text').textContent =
                targetCurrentQuery ? 'Aucun resultat pour "' + targetCurrentQuery + '"' : 'Aucun contact cible';
            return;
        }

        targetEmpty.classList.add('hidden');

        targetList.innerHTML = contacts.map(c => {
            const initials = c.name ? c.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase() : '?';
            const nameHtml = targetCurrentQuery ? highlightMatch(c.name, targetCurrentQuery) : (c.name || 'Inconnu');
            const phoneHtml = targetCurrentQuery ? highlightMatch(c.phone_number, targetCurrentQuery) : (c.phone_number || '');
            return `<div class="flex items-center justify-between px-5 py-2.5 hover:bg-gray-50 transition group border-b border-gray-50 last:border-0" id="cc-${c.id}">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-[11px] font-bold flex-shrink-0">${initials}</div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${nameHtml}</p>
                        <p class="text-[10px] text-gray-400 font-mono">${phoneHtml}</p>
                    </div>
                </div>
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                    ${canSend ? `<button onclick="sendToContact(${c.id}, this)" title="Envoyer" class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-md transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>` : ''}
                    <button onclick="detachContact(${c.id})" title="Retirer" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-md transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>`;
        }).join('');

        // Pagination
        if (data.last_page > 1) {
            targetPagination.classList.remove('hidden');
            let paginHtml = '<div class="flex items-center justify-center gap-1">';
            for (let p = 1; p <= data.last_page; p++) {
                paginHtml += `<button onclick="loadTargetContacts(${p}, '${targetCurrentQuery.replace(/'/g, "\\'")}')"
                    class="px-2.5 py-1 text-xs rounded-md transition ${p === data.current_page ? 'bg-primary-500 text-white font-medium' : 'text-gray-600 hover:bg-gray-200'}">${p}</button>`;
            }
            paginHtml += '</div>';
            paginHtml += `<p class="text-[10px] text-gray-400 text-center mt-1.5">${data.from}-${data.to} sur ${data.total}</p>`;
            targetPagination.innerHTML = paginHtml;
        } else {
            targetPagination.classList.add('hidden');
        }
    }

    // Recherche avec debounce
    if (searchTargets) {
        searchTargets.addEventListener('input', function() {
            const q = this.value.trim();
            clearTimeout(targetSearchTimeout);
            targetSearchTimeout = setTimeout(() => loadTargetContacts(1, q), 300);
        });
    }

    // Chargement initial
    loadTargetContacts(1);

    // ═══ RETIRER UN CONTACT ═══
    window.detachContact = async function(id) {
        try {
            const r = await fetch('{{ route("ajax.campagnes.detachContacts", $campaign) }}', { method: 'DELETE', headers, body: JSON.stringify({ contact_ids: [id] }) });
            const res = await r.json();
            if (res.success) {
                toast('Contact retire');
                loadTargetContacts(targetCurrentPage, targetCurrentQuery);
            }
        } catch(e) { toast('Erreur', 'error'); }
    };

    // ═══ ENVOYER A UN CONTACT ═══
    window.sendToContact = async function(id, btn) {
        btn.disabled = true; btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
        try {
            const r = await fetch('{{ route("ajax.campagnes.sendSingle", $campaign) }}', { method: 'POST', headers, body: JSON.stringify({ contact_id: id }) });
            const res = await r.json();
            toast(res.message || 'Message envoye', res.success ? 'success' : 'error');
            if (res.success) setTimeout(() => location.reload(), 1000);
        } catch(e) { toast('Erreur', 'error'); }
        btn.disabled = false; btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>';
    };

    // ═══ MODALES ═══
    window.openSendModal = () => document.getElementById('send-modal').classList.remove('hidden');
    window.closeSendModal = () => document.getElementById('send-modal').classList.add('hidden');
    window.openScheduleModal = () => { document.getElementById('schedule-modal').classList.remove('hidden'); const n = new Date(); n.setHours(n.getHours()+1); document.getElementById('schedule-datetime').value = n.toISOString().slice(0,16); };
    window.closeScheduleModal = () => document.getElementById('schedule-modal').classList.add('hidden');
    window.confirmDelete = () => document.getElementById('delete-modal').classList.remove('hidden');
    window.closeDeleteModal = () => document.getElementById('delete-modal').classList.add('hidden');

    document.querySelectorAll('#send-modal, #schedule-modal, #delete-modal').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); }));

    window.confirmSend = async function(btn) {
        btnLoad(btn);
        try {
            const r = await fetch('{{ route("ajax.campagnes.send", $campaign) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' } });
            const res = await r.json();
            if (res.success) { closeSendModal(); toast(res.message || 'Envoi lance !'); setTimeout(() => location.reload(), 1000); }
            else { toast(res.message || 'Erreur', 'error'); btnReset(btn); }
        } catch(e) { toast('Erreur reseau', 'error'); btnReset(btn); }
    };

    window.confirmSchedule = async function(btn) {
        const dt = document.getElementById('schedule-datetime').value;
        if (!dt) { toast('Selectionnez une date', 'error'); return; }
        btnLoad(btn);
        try {
            const r = await fetch('{{ route("ajax.campagnes.schedule", $campaign) }}', { method: 'POST', headers, body: JSON.stringify({ scheduled_at: dt }) });
            const res = await r.json();
            if (res.success) { closeScheduleModal(); toast(res.message || 'Campagne planifiee'); setTimeout(() => location.reload(), 1000); }
            else { toast(res.message || 'Erreur', 'error'); btnReset(btn); }
        } catch(e) { toast('Erreur reseau', 'error'); btnReset(btn); }
    };

    window.doCancelSchedule = async function(btn) {
        if (!confirm('Annuler la planification de cette campagne ?')) return;
        btn.disabled = true;
        try {
            const r = await fetch('{{ route("ajax.campagnes.cancelSchedule", $campaign) }}', {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' }
            });
            const res = await r.json();
            if (res.success) { toast(res.message || 'Planification annulee'); setTimeout(() => location.reload(), 800); }
            else { toast(res.message || 'Erreur', 'error'); btn.disabled = false; }
        } catch(e) { toast('Erreur reseau', 'error'); btn.disabled = false; }
    };

    window.doReopen = async function(btn) {
        btnLoad(btn);
        btn.querySelector('.reopen-icon')?.classList.add('hidden');
        try {
            const r = await fetch('{{ route("ajax.campagnes.reopen", $campaign) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' } });
            const res = await r.json();
            if (res.success) { toast(res.message || 'Campagne réouverte'); setTimeout(() => location.reload(), 800); }
            else { toast(res.message || 'Erreur', 'error'); btnReset(btn); btn.querySelector('.reopen-icon')?.classList.remove('hidden'); }
        } catch(e) { toast('Erreur réseau', 'error'); btnReset(btn); btn.querySelector('.reopen-icon')?.classList.remove('hidden'); }
    };

    window.doDelete = async function(btn) {
        btnLoad(btn);
        try {
            const r = await fetch('{{ route("ajax.campagnes.destroy", $campaign) }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' } });
            const res = await r.json();
            if (res.success) { toast('Campagne supprimee'); setTimeout(() => window.location.href = '{{ route("campagnes.index") }}', 800); }
            else { toast('Erreur', 'error'); btnReset(btn); }
        } catch(e) { toast('Erreur', 'error'); btnReset(btn); }
    };

    // ═══ RAFRAICHIR STATUTS ═══
    window.refreshStatuses = async function(btn) {
        const icon = btn.querySelector('.refresh-icon');
        if (icon) icon.classList.add('animate-spin');
        btn.disabled = true;
        try {
            const r = await fetch('{{ route("ajax.campagnes.refreshStatuses", $campaign) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' } });
            const res = await r.json();
            if (res.success) { toast(`${res.updated} statut(s) mis a jour`); if (res.updated > 0) setTimeout(() => location.reload(), 800); }
        } catch(e) { toast('Erreur', 'error'); }
        btn.disabled = false;
        if (icon) icon.classList.remove('animate-spin');
    };
})();
</script>
@endpush
