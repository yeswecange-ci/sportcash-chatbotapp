@extends('layouts.app')
@section('title', 'Campagnes')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Campagnes WhatsApp</h1>
                <p class="text-sm text-gray-500 mt-0.5">Gerez vos campagnes de push messages</p>
            </div>
            <a href="{{ route('campagnes.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-500 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvelle campagne
            </a>
        </div>

        {{-- Recherche --}}
        <div class="mb-5">
            <form method="GET" class="relative max-w-xs">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une campagne..."
                       class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm">
            </form>
        </div>

        @if($campaigns->count())
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($campaigns as $campaign)
            <a href="{{ route('campagnes.show', $campaign) }}"
               class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-primary-200 transition group block">
                <div class="flex items-start justify-between mb-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition truncate">{{ $campaign->name }}</h3>
                        @if($campaign->description)
                        <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $campaign->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-1.5 ml-2 flex-shrink-0">
                        @if($campaign->hasPendingSchedule())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-700" title="{{ $campaign->scheduled_at->format('d/m/Y H:i') }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $campaign->scheduled_at->format('d/m H:i') }}
                        </span>
                        @endif
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $campaign->statusBadgeClass() }}">
                            {{ $campaign->statusLabel() }}
                        </span>
                    </div>
                </div>

                @if($campaign->template_name)
                <div class="bg-gray-50 rounded-lg px-3 py-2 mb-3">
                    <p class="text-[11px] text-gray-500 flex items-center gap-1.5 truncate">
                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ $campaign->template_name }}
                    </p>
                </div>
                @endif

                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-1.5 text-gray-500">
                        <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="font-semibold text-gray-700">{{ $campaign->contacts_count }}</span> contact(s)
                    </div>
                    <div class="flex items-center gap-1.5 text-gray-500">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="font-semibold text-gray-700">{{ $campaign->messages_count }}</span> envoi(s)
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-gray-100 text-[11px] text-gray-400">
                    {{ $campaign->created_at->format('d/m/Y a H:i') }}
                </div>
            </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $campaigns->withQueryString()->links() }}</div>
        @else
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-primary-50 flex items-center justify-center">
                <svg class="w-7 h-7 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-1">Aucune campagne</h3>
            <p class="text-sm text-gray-400 mb-5">Creez votre premiere campagne WhatsApp</p>
            <a href="{{ route('campagnes.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Creer une campagne
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
