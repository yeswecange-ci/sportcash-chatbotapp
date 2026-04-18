@extends('layouts.app')
@section('title', 'Kash Bot — Historique conversations')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50 h-full">
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Historique conversations Kash</h1>
            <p class="text-sm text-gray-500 mt-0.5">Tous les échanges entre l'agent IA et les clients WhatsApp</p>
        </div>
        <a href="{{ route('kash.dashboard') }}"
           class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
            ← Dashboard
        </a>
    </div>

    <div class="flex gap-4 h-[calc(100vh-180px)]">

        {{-- LISTE DES CONVERSATIONS (colonne gauche) --}}
        <div class="w-72 flex-shrink-0 bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Conversations</p>
            </div>
            <div class="overflow-y-auto flex-1">
                @forelse($conversations as $conv)
                    @php $phone = str_replace('whatsapp:', '', $conv->sender); @endphp
                    <a href="{{ route('kash.historique') }}?sender={{ urlencode($conv->sender) }}"
                       class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-50 transition
                              {{ $sender === $conv->sender ? 'bg-indigo-50 border-l-2 border-l-indigo-500' : '' }}">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="text-xs font-bold text-indigo-600">{{ strtoupper(substr($phone, -2)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $phone }}</p>
                            <p class="text-[11px] text-gray-400">
                                {{ $conv->total }} msg · {{ \Carbon\Carbon::parse($conv->last_at)->diffForHumans() }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-gray-400">Aucune conversation</div>
                @endforelse
            </div>
            {{-- Pagination --}}
            @if($conversations->hasPages())
            <div class="px-4 py-2 border-t border-gray-100 text-xs text-gray-400 flex justify-between">
                @if($conversations->onFirstPage())
                    <span>←</span>
                @else
                    <a href="{{ $conversations->previousPageUrl() }}" class="text-indigo-600 hover:underline">←</a>
                @endif
                <span>{{ $conversations->currentPage() }} / {{ $conversations->lastPage() }}</span>
                @if($conversations->hasMorePages())
                    <a href="{{ $conversations->nextPageUrl() }}" class="text-indigo-600 hover:underline">→</a>
                @else
                    <span>→</span>
                @endif
            </div>
            @endif
        </div>

        {{-- MESSAGES (colonne droite) --}}
        <div class="flex-1 bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            @if($sender && $messages->isNotEmpty())
                {{-- Header conversation --}}
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ str_replace('whatsapp:', '', $sender) }}</p>
                        <p class="text-xs text-gray-400">{{ $messages->count() }} messages</p>
                    </div>
                    @php
                        $hasEscalade = \App\Models\BotEscalade::where('sender', $sender)->exists();
                        $hasReclamation = \App\Models\BotReclamation::where('sender', $sender)->exists();
                    @endphp
                    <div class="flex gap-2">
                        @if($hasReclamation)
                            <a href="{{ route('kash.reclamations.index') }}?search={{ urlencode(str_replace('whatsapp:', '', $sender)) }}"
                               class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-lg hover:bg-blue-100">
                                Voir réclamations
                            </a>
                        @endif
                        @if($hasEscalade)
                            <a href="{{ route('kash.escalades.index') }}?search={{ urlencode(str_replace('whatsapp:', '', $sender)) }}"
                               class="text-xs bg-amber-50 text-amber-700 px-2 py-1 rounded-lg hover:bg-amber-100">
                                Voir escalades
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Messages --}}
                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3" id="messages-container">
                    @foreach($messages as $msg)
                        <div class="flex {{ $msg->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[75%]">
                                <div class="rounded-2xl px-4 py-2.5 text-sm
                                    {{ $msg->direction === 'outbound'
                                        ? 'bg-indigo-600 text-white rounded-br-sm'
                                        : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
                                    {{ $msg->content }}
                                </div>
                                <div class="flex items-center gap-2 mt-1
                                    {{ $msg->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                                    <span class="text-[10px] text-gray-400">
                                        {{ $msg->created_at->format('d/m H:i') }}
                                    </span>
                                    @if($msg->signal_type !== 'none')
                                        <span class="text-[10px] px-1.5 py-0.5 rounded
                                            {{ $msg->signal_type === 'escalade'
                                                ? 'bg-amber-100 text-amber-700'
                                                : 'bg-blue-100 text-blue-700' }}">
                                            {{ $msg->signal_type }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            @elseif($sender)
                <div class="flex-1 flex items-center justify-center text-gray-400 text-sm">
                    Aucun message pour ce contact.
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="text-sm">Sélectionne une conversation</p>
                </div>
            @endif
        </div>

    </div>

</div>
</div>

<script>
    // Auto-scroll vers le bas des messages
    const container = document.getElementById('messages-container');
    if (container) container.scrollTop = container.scrollHeight;
</script>
@endsection
