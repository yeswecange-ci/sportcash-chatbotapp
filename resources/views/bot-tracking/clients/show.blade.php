@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

        {{-- Back + Header --}}
        <div class="flex items-center justify-between gap-3 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('bot-tracking.clients.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Clients
                </a>
                <span class="text-gray-300">/</span>
                <span class="text-sm font-medium text-gray-900">{{ $client->client_full_name ?? $client->whatsapp_profile_name ?? $client->phone_number }}</span>
            </div>
            @if(auth()->user()?->isAdmin())
            <a href="{{ route('bot-tracking.clients.edit', $client->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Modifier
            </a>
            @endif
        </div>

        {{-- Client Header Card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-2xl font-bold text-indigo-700">{{ strtoupper(substr($client->client_full_name ?? $client->whatsapp_profile_name ?? '?', 0, 1)) }}</span>
                </div>
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-gray-900">{{ $client->client_full_name ?? $client->whatsapp_profile_name ?? 'Inconnu' }}</h1>
                        @if($client->is_client === true || $client->is_client == 1)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Client SportCash</span>
                        @elseif($client->is_client === false || $client->is_client == 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Non-client</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Non defini</span>
                        @endif
                        @if($client->carte_vip)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">VIP</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">{{ $client->phone_number }}</p>
                    @if($client->email)<p class="text-sm text-gray-400">{{ $client->email }}</p>@endif
                </div>
            </div>
        </div>

        {{-- 4 Stat Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($interactionStats['total_messages'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Messages</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($interactionStats['menu_choices'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Choix menu</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                @php $avgMin = ($interactionStats['avg_duration'] ?? 0) > 0 ? round(($interactionStats['avg_duration'] ?? 0) / 60) : 0; @endphp
                <p class="text-2xl font-bold text-blue-600">{{ $avgMin }}<span class="text-sm font-normal">min</span></p>
                <p class="text-xs text-gray-500 mt-0.5">Duree moy.</p>
            </div>
        </div>

        {{-- Two-Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- Client Info Panel --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Informations</h3>
                <div class="space-y-3">
                    <div><p class="text-xs text-gray-400 uppercase tracking-wide">Telephone</p><p class="text-sm text-gray-700 mt-0.5">{{ $client->phone_number }}</p></div>
                    <div><p class="text-xs text-gray-400 uppercase tracking-wide">Email</p><p class="text-sm text-gray-700 mt-0.5">{{ $client->email ?: '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 uppercase tracking-wide">Profil WhatsApp</p><p class="text-sm text-gray-700 mt-0.5">{{ $client->whatsapp_profile_name ?: '—' }}</p></div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Derniere interaction</p>
                        <p class="text-sm text-gray-700 mt-0.5">
                            @if($client->last_interaction_at)
                                {{ \Carbon\Carbon::parse($client->last_interaction_at)->diffForHumans() }}
                            @else
                                —
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Conversations History --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Historique des conversations ({{ $conversations->total() }})</h3>
                <div class="space-y-3">
                    @forelse($conversations as $conv)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            @php $sm2 = ['active' => 'bg-green-100 text-green-700', 'completed' => 'bg-blue-100 text-blue-700', 'timeout' => 'bg-amber-100 text-amber-700', 'abandoned' => 'bg-gray-100 text-gray-600']; @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sm2[$conv->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($conv->status) }}</span>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $conv->current_menu ?: '—' }} &bull;
                                @if($conv->duration_seconds){{ round($conv->duration_seconds / 60) }}min
                                @else —
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">@if($conv->started_at){{ \Carbon\Carbon::parse($conv->started_at)->format('d/m/Y') }}@endif</p>
                            <a href="{{ route('bot-tracking.conversations.show', $conv->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Voir &rarr;</a>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-4">Aucune conversation.</p>
                    @endforelse
                </div>
                @if($conversations->hasPages())
                <div class="mt-3 pt-3 border-t border-gray-100">{{ $conversations->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Full Events Timeline --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-6">Tous les evenements ({{ $allEvents->total() }})</h3>
            @if($allEvents->isEmpty())
                <p class="text-sm text-gray-400 text-center py-8">Aucun evenement.</p>
            @else
            <div class="relative pl-6 space-y-5">
                <div class="absolute left-2 top-2 bottom-2 w-px bg-gray-200"></div>
                @foreach($allEvents as $event)
                @php
                    $tc = ['menu_choice' => 'bg-indigo-500', 'free_input' => 'bg-purple-500', 'message_received' => 'bg-green-500', 'message_sent' => 'bg-blue-500', 'agent_transfer' => 'bg-amber-500', 'error' => 'bg-red-500', 'invalid_input' => 'bg-red-400'];
                    $dot = $tc[$event->event_type] ?? 'bg-gray-400';
                    $ts  = $event->event_at ?? $event->created_at;
                @endphp
                <div class="relative">
                    <div class="absolute -left-6 mt-1 w-3 h-3 rounded-full {{ $dot }} ring-2 ring-white"></div>
                    <div class="pl-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ str_replace(['_'], ' ', $event->event_type) }}</span>
                            <span class="text-xs text-gray-400">{{ $ts ? \Carbon\Carbon::parse($ts)->format('d/m/Y H:i:s') : '' }}</span>
                        </div>
                        @if($event->user_input)
                        <div class="bg-gray-50 rounded-lg px-3 py-2 mb-1 border-l-2 border-indigo-400">
                            <p class="text-xs text-gray-400 mb-0.5">Saisie</p><p class="text-sm text-gray-800">{{ $event->user_input }}</p>
                        </div>
                        @endif
                        @if($event->bot_message)
                        <div class="bg-blue-50 rounded-lg px-3 py-2 mb-1 border-l-2 border-blue-400">
                            <p class="text-xs text-blue-400 mb-0.5">Bot</p><p class="text-sm text-gray-800">{{ $event->bot_message }}</p>
                        </div>
                        @endif
                        @if($event->widget_name)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Widget: {{ $event->widget_name }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @if($allEvents->hasPages())
            <div class="mt-4 border-t border-gray-100 pt-4">{{ $allEvents->links() }}</div>
            @endif
            @endif
        </div>

    </div>
</div>
@endsection
