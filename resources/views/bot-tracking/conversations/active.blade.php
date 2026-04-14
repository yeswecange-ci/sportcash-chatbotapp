@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Conversations actives</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $activeConversations->count() }} conversation(s) en cours en ce moment</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.location.reload()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Actualiser
                </button>
                <a href="{{ route('bot-tracking.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                    &larr; Dashboard
                </a>
            </div>
        </div>

        @if($activeConversations->isEmpty())
        {{-- Empty State --}}
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Aucune conversation active</h3>
            <p class="text-sm text-gray-500">Il n'y a pas de conversations en cours pour le moment.</p>
            <button onclick="window.location.reload()" class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">Actualiser la page</button>
        </div>
        @else
        {{-- Active Conversations Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($activeConversations as $conv)
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-semibold text-indigo-700">{{ strtoupper(substr($conv->display_name ?? '?', 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $conv->display_name ?? 'Inconnu' }}</p>
                            <p class="text-xs text-gray-400">{{ $conv->phone_number }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Actif
                    </span>
                </div>
                <div class="space-y-1.5 mb-4">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Type</span>
                        @if($conv->is_client === true || $conv->is_client == 1)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Client</span>
                        @elseif($conv->is_client === false || $conv->is_client == 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Non-client</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inconnu</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Menu actuel</span>
                        <span class="font-medium text-gray-700">{{ $conv->current_menu ?? '&mdash;' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Debut</span>
                        <span class="text-gray-600">{{ $conv->started_at ? \Carbon\Carbon::parse($conv->started_at)->diffForHumans() : '&mdash;' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Derniere activite</span>
                        <span class="text-gray-600">{{ $conv->last_activity_at ? \Carbon\Carbon::parse($conv->last_activity_at)->diffForHumans() : '&mdash;' }}</span>
                    </div>
                </div>
                <a href="{{ route('bot-tracking.conversations.show', $conv->id) }}"
                   class="inline-flex w-full items-center justify-center gap-2 px-3 py-2 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-lg hover:bg-indigo-100 transition">
                    Voir le detail &rarr;
                </a>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>
@endsection
