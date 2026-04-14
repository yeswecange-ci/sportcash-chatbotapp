@extends('layouts.app')

@section('title', $game->name)

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 mb-5">
            <a href="{{ route('gamification.index') }}"
               class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Gamification
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-sm font-medium text-gray-700">{{ $game->name }}</span>
        </div>

        @if(session('success'))
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Header card --}}
        @php
            $typeConfig = [
                'quiz'       => ['label' => 'Quiz',          'class' => 'bg-violet-100 text-violet-700', 'icon_color' => 'bg-violet-100'],
                'free_text'  => ['label' => 'Réponse libre', 'class' => 'bg-blue-100 text-blue-700',   'icon_color' => 'bg-blue-100'],
                'vote'       => ['label' => 'Vote',          'class' => 'bg-amber-100 text-amber-700', 'icon_color' => 'bg-amber-100'],
                'prediction' => ['label' => 'Pronostic',     'class' => 'bg-indigo-100 text-indigo-700','icon_color' => 'bg-indigo-100'],
            ];
            $tc = $typeConfig[$game->type] ?? ['label' => $game->type, 'class' => 'bg-gray-100 text-gray-600', 'icon_color' => 'bg-gray-100'];
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

                {{-- Identité du jeu --}}
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl {{ $tc['icon_color'] }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h1 class="text-xl font-bold text-gray-900">{{ $game->name }}</h1>
                            @if($game->status === 'active')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>Actif
                                </span>
                            @elseif($game->status === 'closed')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Clôturé
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>Brouillon
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tc['class'] }}">{{ $tc['label'] }}</span>
                        </div>
                        <p class="text-sm text-gray-400 font-mono mt-0.5">{{ $game->slug }}</p>
                        @if($game->start_date || $game->end_date)
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $game->start_date?->format('d/m/Y') ?? '—' }}
                            <span class="mx-1">→</span>
                            {{ $game->end_date?->format('d/m/Y') ?? '∞' }}
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap items-center gap-2">
                    @if($game->status !== 'active')
                    <form method="POST" action="{{ route('gamification.activate', $game->slug) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Activer
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('gamification.close', $game->slug) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Clôturer
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('gamification.edit', $game->slug) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Éditer
                    </a>
                    <form method="POST" action="{{ route('gamification.duplicate', $game->slug) }}">
                        @csrf
                        <button type="submit" title="Dupliquer ce jeu"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Dupliquer
                        </button>
                    </form>
                    @if($stats['completed'] > 0)
                    <a href="{{ route('gamification.leaderboard', $game->slug) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-amber-600 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        Classement
                    </a>
                    @endif
                    <a href="{{ route('gamification.flow', $game->slug) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        Flow Studio
                    </a>
                    <a href="{{ route('gamification.export', $game->slug) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-white bg-violet-600 rounded-lg hover:bg-violet-700 transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export CSV
                    </a>
                </div>
            </div>

            {{-- Stats rapides --}}
            <div class="grid grid-cols-4 gap-4 mt-5 pt-5 border-t border-gray-100">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Participants</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Complétés</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-amber-500">{{ $stats['started'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">En cours</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-400">{{ $stats['abandoned'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Abandonnés</p>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

            <div class="border-b border-gray-200 px-1">
                <nav class="flex -mb-px" id="tab-nav">
                    <button type="button" onclick="switchTab('apercu')" id="tab-apercu"
                            class="tab-btn px-5 py-3.5 text-sm font-medium text-violet-600 border-b-2 border-violet-600 transition">
                        Aperçu
                    </button>
                    <button type="button" onclick="switchTab('participants')" id="tab-participants"
                            class="tab-btn px-5 py-3.5 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent transition">
                        Participants &amp; Réponses
                        @if($stats['total'] > 0)
                        <span class="ml-1.5 px-1.5 py-0.5 text-xs bg-gray-100 text-gray-500 rounded-full">{{ $stats['total'] }}</span>
                        @endif
                    </button>
                    <button type="button" onclick="switchTab('stats')" id="tab-stats"
                            class="tab-btn px-5 py-3.5 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent transition">
                        Statistiques
                    </button>
                </nav>
            </div>

            {{-- Onglet Aperçu --}}
            <div id="pane-apercu" class="p-5">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Questions --}}
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-700">
                                Questions
                                <span class="ml-1.5 text-xs font-normal text-gray-400">({{ $game->questions->count() }})</span>
                            </h3>
                            <a href="{{ route('gamification.edit', $game->slug) }}"
                               class="inline-flex items-center gap-1 text-xs font-medium text-violet-600 hover:text-violet-700 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Gérer les questions
                            </a>
                        </div>

                        @forelse($game->questions->sortBy('order') as $question)
                        <div class="py-3.5 border-b border-gray-100 last:border-0">
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                                    {{ $question->order }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded font-medium">{{ $question->type }}</span>
                                    </div>
                                    <p class="text-sm text-gray-800">{{ $question->text }}</p>
                                    @if($question->options)
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($question->options as $opt)
                                        <span class="text-xs px-2 py-0.5 bg-violet-50 text-violet-600 border border-violet-100 rounded-full">{{ $opt }}</span>
                                        @endforeach
                                    </div>
                                    @endif
                                    @if($question->correct_answer)
                                    <p class="text-xs text-green-600 mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ $question->correct_answer }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="py-10 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <p class="text-sm text-gray-400">Aucune question</p>
                            <a href="{{ route('gamification.edit', $game->slug) }}"
                               class="mt-2 inline-block text-xs text-violet-600 hover:underline">
                                Ajouter des questions →
                            </a>
                        </div>
                        @endforelse
                    </div>

                    {{-- Infos du jeu --}}
                    <div class="border-t lg:border-t-0 lg:border-l border-gray-100 pt-4 lg:pt-0 lg:pl-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Informations</h3>
                        <dl class="space-y-3.5">
                            @if($game->description)
                            <div>
                                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Description</dt>
                                <dd class="mt-1 text-sm text-gray-700">{{ $game->description }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Éligibilité</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($game->eligibility === 'all')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                                            @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            @endif
                                        </svg>
                                        {{ $game->eligibility === 'all' ? 'Tout le monde' : 'Clients uniquement' }}
                                    </span>
                                </dd>
                            </div>
                            @if($game->start_date || $game->end_date)
                            <div>
                                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Période</dt>
                                <dd class="mt-1 text-sm text-gray-700">
                                    {{ $game->start_date?->format('d/m/Y H:i') ?? '—' }}
                                    <span class="text-gray-300 mx-1">→</span>
                                    {{ $game->end_date?->format('d/m/Y H:i') ?? '∞' }}
                                </dd>
                            </div>
                            @endif
                            @if($game->max_participants)
                            <div>
                                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Limite</dt>
                                <dd class="mt-1 text-sm text-gray-700">{{ number_format($game->max_participants) }} participants max.</dd>
                            </div>
                            @endif
                            @if($game->thank_you_message)
                            <div>
                                <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Message de fin</dt>
                                <dd class="mt-1 text-sm text-gray-600 italic bg-gray-50 rounded-lg px-3 py-2 border-l-2 border-violet-200">
                                    {{ $game->thank_you_message }}
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                </div>
            </div>

            {{-- Onglet Participants --}}
            <div id="pane-participants" class="hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="participant-search"
                           placeholder="Rechercher par nom ou téléphone..."
                           oninput="filterParticipants(this.value)"
                           class="flex-1 max-w-sm text-sm bg-transparent border-0 outline-none text-gray-700 placeholder-gray-400">
                </div>

                @if($participations->isEmpty())
                <div class="py-16 text-center">
                    <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-400">Aucun participant pour l'instant</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100" id="participants-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participant</th>
                                <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Début</th>
                                <th class="px-4 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50" id="participants-body">
                            @php $totalQuestions = $game->questions->count(); @endphp
                            @foreach($participations as $p)
                            @php
                                $correctCount = $p->answers->where('is_correct', true)->count();
                                $hasScore     = $p->answers->whereNotNull('is_correct')->count() > 0;
                            @endphp
                            <tr class="hover:bg-gray-50 participant-row"
                                data-search="{{ strtolower($p->participant_name . ' ' . $p->phone_number) }}"
                                data-pid="{{ $p->id }}">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center flex-shrink-0">
                                            <span class="text-xs font-bold text-violet-600">
                                                {{ strtoupper(substr($p->participant_name ?? '?', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $p->participant_name ?? '—' }}</p>
                                            <p class="text-xs text-gray-400 font-mono">{{ $p->phone_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($p->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Complété
                                        </span>
                                    @elseif($p->status === 'abandoned')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Abandonné
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>En cours
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($hasScore && $totalQuestions > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold {{ $correctCount === $totalQuestions ? 'text-green-600' : 'text-gray-700' }}">
                                                {{ $correctCount }}/{{ $totalQuestions }}
                                            </span>
                                            <div class="w-16 bg-gray-100 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full {{ $correctCount === $totalQuestions ? 'bg-green-500' : 'bg-violet-400' }}"
                                                     style="width: {{ $totalQuestions > 0 ? round($correctCount / $totalQuestions * 100) : 0 }}%"></div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-xs text-gray-400">
                                    {{ $p->started_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($p->answers->isNotEmpty())
                                    <button onclick="toggleAnswers({{ $p->id }})"
                                            class="text-xs font-medium text-violet-600 hover:text-violet-700 hover:underline whitespace-nowrap">
                                        Voir réponses ↓
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @if($p->answers->isNotEmpty())
                            <tr id="answers-{{ $p->id }}" class="hidden">
                                <td colspan="5" class="px-4 py-3 bg-violet-50 border-l-2 border-violet-300">
                                    <div class="space-y-2 pl-11">
                                        @foreach($p->answers->sortBy(fn($a) => $a->question?->order) as $answer)
                                        <div class="flex items-center gap-3 text-sm">
                                            <span class="font-semibold text-violet-600 flex-shrink-0 w-8 text-right">
                                                Q{{ $answer->question?->order }}
                                            </span>
                                            <span class="text-gray-300">·</span>
                                            <span class="text-gray-700 flex-1">{{ $answer->answer_text }}</span>
                                            @if($answer->is_correct === true)
                                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    Correct
                                                </span>
                                            @elseif($answer->is_correct === false)
                                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Incorrect
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-300">—</span>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if($participations->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                    {{ $participations->links() }}
                </div>
                @endif
            </div>

            {{-- Onglet Statistiques --}}
            <div id="pane-stats" class="hidden p-5">

                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Taux de complétion</span>
                        <span class="text-sm font-bold text-gray-900">{{ $stats['completion_rate'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full transition-all {{ $stats['completion_rate'] >= 70 ? 'bg-green-500' : ($stats['completion_rate'] >= 40 ? 'bg-amber-400' : 'bg-violet-500') }}"
                             style="width: {{ $stats['completion_rate'] }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">{{ $stats['completed'] }} complétés sur {{ $stats['total'] }} participants</p>
                </div>

                @if($game->questions->isNotEmpty())
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Score par question</h3>
                    <div class="space-y-4">
                        @foreach($game->questions->sortBy('order') as $question)
                        @php $qs = $questionStats[$question->id] ?? null; @endphp
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded bg-violet-100 text-violet-700 flex items-center justify-center text-xs font-bold">{{ $question->order }}</span>
                                    <span class="text-sm text-gray-700">{{ mb_substr($question->text, 0, 60) }}{{ strlen($question->text) > 60 ? '...' : '' }}</span>
                                </div>
                                @if($qs && $qs['total'] > 0 && $qs['rate'] !== null)
                                    <span class="text-sm font-bold ml-4 flex-shrink-0 {{ $qs['rate'] >= 60 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ $qs['correct'] }}/{{ $qs['total'] }}
                                        <span class="text-xs font-normal text-gray-400">({{ $qs['rate'] }}%)</span>
                                    </span>
                                @elseif($qs && $qs['total'] > 0)
                                    <span class="text-xs text-gray-400 ml-4">{{ $qs['total'] }} réponse(s)</span>
                                @else
                                    <span class="text-xs text-gray-300 ml-4">Aucune réponse</span>
                                @endif
                            </div>
                            @if($qs && $qs['total'] > 0 && $qs['rate'] !== null)
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $qs['rate'] >= 60 ? 'bg-green-500' : 'bg-red-400' }}"
                                     style="width: {{ $qs['rate'] }}%"></div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="pt-4 border-t border-gray-100">
                    <a href="{{ route('gamification.statistics', $game->slug) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-violet-600 border border-violet-200 bg-violet-50 rounded-lg hover:bg-violet-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Voir les statistiques complètes
                    </a>
                </div>

            </div>

        </div>

    </div>
</div>

@push('scripts')
<script>
function switchTab(name) {
    ['apercu', 'participants', 'stats'].forEach(function(t) {
        document.getElementById('pane-' + t).classList.add('hidden');
        var btn = document.getElementById('tab-' + t);
        btn.classList.remove('text-violet-600', 'border-violet-600');
        btn.classList.add('text-gray-500', 'border-transparent');
    });
    document.getElementById('pane-' + name).classList.remove('hidden');
    var activeBtn = document.getElementById('tab-' + name);
    activeBtn.classList.remove('text-gray-500', 'border-transparent');
    activeBtn.classList.add('text-violet-600', 'border-violet-600');
}

function toggleAnswers(id) {
    var row = document.getElementById('answers-' + id);
    row.classList.toggle('hidden');
    var btn = document.querySelector('[onclick="toggleAnswers(' + id + ')"]');
    if (btn) btn.textContent = row.classList.contains('hidden') ? 'Voir réponses ↓' : 'Masquer ↑';
}

function filterParticipants(q) {
    var lower = q.toLowerCase();
    document.querySelectorAll('.participant-row').forEach(function(row) {
        var search  = row.dataset.search || '';
        var visible = search.includes(lower);
        row.style.display = visible ? '' : 'none';
        var pid = row.dataset.pid;
        if (pid) {
            var answerRow = document.getElementById('answers-' + pid);
            if (answerRow) answerRow.style.display = visible ? '' : 'none';
        }
    });
}
</script>
@endpush
@endsection
