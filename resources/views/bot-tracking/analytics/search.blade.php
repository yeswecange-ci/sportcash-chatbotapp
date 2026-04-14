@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Recherche de saisies</h1>
                <p class="text-sm text-gray-500 mt-0.5">Recherchez dans les messages et saisies libres du bot</p>
            </div>
            <a href="{{ route('bot-tracking.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">&larr; Dashboard</a>
        </div>

        {{-- Search Form --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <form method="GET" action="{{ route('bot-tracking.search') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex flex-col gap-1 flex-1 min-w-64">
                    <label class="text-xs font-medium text-gray-600">Texte recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher dans les messages..." autofocus
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Rechercher
                </button>
            </form>
        </div>
        {{-- Results --}}
        @if(request()->has('search') && request('search'))
        <div class="mb-4">
            <p class="text-sm text-gray-500">{{ $freeInputs->total() }} resultat(s) pour &laquo; <strong>{{ request('search') }}</strong> &raquo;</p>
        </div>
        @endif

        @if($freeInputs->isEmpty() && request()->has('search'))
        {{-- No results --}}
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="text-base font-medium text-gray-900 mb-1">Aucun resultat</h3>
            <p class="text-sm text-gray-500">Essayez avec d'autres mots-cles ou une plage de dates differente.</p>
        </div>
        @elseif(!$freeInputs->isEmpty())
        {{-- Results list --}}
        <div class="space-y-4">
            @foreach($freeInputs as $input)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                {{-- User Input --}}
                @if($input->user_input)
                <div class="border-l-4 border-purple-400 pl-4 mb-3">
                    <p class="text-xs font-medium text-purple-600 mb-0.5">Saisie utilisateur</p>
                    <p class="text-sm text-gray-800">{{ $input->user_input }}</p>
                </div>
                @endif

                {{-- Bot Response --}}
                @if($input->bot_message)
                <div class="border-l-4 border-blue-400 pl-4 mb-3">
                    <p class="text-xs font-medium text-blue-600 mb-0.5">Reponse bot</p>
                    <p class="text-sm text-gray-700">{{ $input->bot_message }}</p>
                </div>
                @endif
                {{-- Meta info --}}
                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100">
                    <div class="flex flex-wrap items-center gap-3">
                        @if($input->conversation)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-xs text-gray-500">{{ $input->conversation->phone_number }}</span>
                        </div>
                        @if($input->conversation->display_name)
                        <span class="text-xs text-gray-500">{{ $input->conversation->display_name }}</span>
                        @endif
                        @if($input->conversation->is_client === true || $input->conversation->is_client == 1)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">Client</span>
                        @elseif($input->conversation->is_client === false || $input->conversation->is_client == 0)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700">Non-client</span>
                        @endif
                        <a href="{{ route('bot-tracking.conversations.show', $input->conversation->id) }}" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium">Voir conversation &rarr;</a>
                        @endif

                        @if($input->widget_name)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">{{ $input->widget_name }}</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-400">
                        @php $ts = $input->event_at ?? $input->created_at; @endphp
                        {{ $ts ? \Carbon\Carbon::parse($ts)->format('d/m/Y H:i') : '' }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($freeInputs->hasPages())
        <div class="mt-6">{{ $freeInputs->appends(request()->query())->links() }}</div>
        @endif
        @else
        {{-- Empty state (no search yet) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="text-base font-medium text-gray-900 mb-1">Lancez une recherche</h3>
            <p class="text-sm text-gray-500">Entrez des mots-cles pour rechercher dans les saisies des utilisateurs.</p>
        </div>
        @endif

    </div>
</div>
@endsection
