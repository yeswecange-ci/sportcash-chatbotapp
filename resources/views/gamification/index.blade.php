@extends('layouts.app')

@section('title', 'Gamification')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gamification</h1>
                <p class="text-sm text-gray-500 mt-0.5">Gérez vos jeux et concours WhatsApp</p>
            </div>
            <a href="{{ route('gamification.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau jeu
            </a>
        </div>

        @if(session('success'))
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Stat cards --}}
        @php
            $globalRate = $globalStats['total_participants'] > 0
                ? round($globalStats['total_completed'] / $globalStats['total_participants'] * 100)
                : 0;
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Jeux actifs</p>
                    <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $globalStats['active_count'] }}</p>
                <p class="text-xs text-gray-400 mt-1">sur {{ $globalStats['total_games'] }} jeux au total</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Participants</p>
                    <div class="w-9 h-9 bg-violet-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($globalStats['total_participants']) }}</p>
                <p class="text-xs text-gray-400 mt-1">toutes campagnes confondues</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Taux complétion</p>
                    <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $globalRate }}%</p>
                <p class="text-xs text-gray-400 mt-1">{{ number_format($globalStats['total_completed']) }} complétés</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total jeux</p>
                    <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $globalStats['total_games'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $globalStats['total_games'] - $globalStats['active_count'] }} inactif{{ ($globalStats['total_games'] - $globalStats['active_count']) > 1 ? 's' : '' }}</p>
            </div>
        </div>

        {{-- Games table --}}
        @php
            $typeConfig = [
                'quiz'       => ['label' => 'Quiz',          'class' => 'bg-violet-100 text-violet-700'],
                'free_text'  => ['label' => 'Réponse libre', 'class' => 'bg-blue-100 text-blue-700'],
                'vote'       => ['label' => 'Vote',          'class' => 'bg-amber-100 text-amber-700'],
                'prediction' => ['label' => 'Pronostic',     'class' => 'bg-indigo-100 text-indigo-700'],
            ];
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Tous les jeux</h2>
                @if($games->isNotEmpty())
                <span class="text-xs text-gray-400 bg-gray-50 border border-gray-100 px-2.5 py-1 rounded-full">
                    {{ $games->count() }} jeu{{ $games->count() > 1 ? 'x' : '' }}
                </span>
                @endif
            </div>

            @if($games->isEmpty())
            <div class="py-20 text-center">
                <div class="w-16 h-16 bg-violet-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-600">Aucun jeu pour l'instant</p>
                <p class="text-xs text-gray-400 mt-1">Créez votre premier jeu pour commencer</p>
                <a href="{{ route('gamification.create') }}"
                   class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-violet-600 rounded-lg hover:bg-violet-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Créer un jeu
                </a>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jeu</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Progression</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                            <th class="px-4 py-3.5 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($games as $game)
                        @php
                            $tc  = $typeConfig[$game->type] ?? ['label' => $game->type, 'class' => 'bg-gray-100 text-gray-600'];
                            $pct = $game->participations_count > 0
                                ? round($game->completed_count / $game->participations_count * 100)
                                : 0;
                            $barColor = $pct >= 70 ? 'bg-green-500' : ($pct >= 40 ? 'bg-amber-400' : 'bg-violet-400');
                            $pctColor = $pct >= 70 ? 'text-green-600' : ($pct >= 40 ? 'text-amber-600' : 'text-gray-500');
                        @endphp
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="px-5 py-4">
                                <a href="{{ route('gamification.show', $game->slug) }}"
                                   class="font-semibold text-sm text-gray-900 group-hover:text-violet-600 transition">
                                    {{ $game->name }}
                                </a>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $game->slug }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tc['class'] }}">
                                    {{ $tc['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                @if($game->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>Actif
                                    </span>
                                @elseif($game->status === 'closed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Clôturé
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>Brouillon
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if($game->participations_count > 0)
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs text-gray-500">
                                            <span class="font-semibold text-gray-700">{{ $game->completed_count }}</span>/{{ $game->participations_count }}
                                        </span>
                                        <span class="text-xs font-semibold {{ $pctColor }}">{{ $pct }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                                @else
                                    <span class="text-xs text-gray-300 italic">Aucun participant</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if($game->start_date || $game->end_date)
                                <div class="text-xs text-gray-500">
                                    {{ $game->start_date?->format('d/m/Y') ?? '—' }}
                                    <span class="text-gray-300 mx-1">→</span>
                                    {{ $game->end_date?->format('d/m/Y') ?? '∞' }}
                                </div>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-0.5">
                                    <a href="{{ route('gamification.show', $game->slug) }}" title="Voir"
                                       class="p-1.5 text-gray-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('gamification.statistics', $game->slug) }}" title="Statistiques"
                                       class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    </a>
                                    <a href="{{ route('gamification.edit', $game->slug) }}" title="Éditer"
                                       class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <a href="{{ route('gamification.export', $game->slug) }}" title="Export CSV"
                                       class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                    <span class="w-px h-4 bg-gray-200 mx-1"></span>
                                    @if($game->status !== 'active')
                                    <form method="POST" action="{{ route('gamification.activate', $game->slug) }}" class="inline">
                                        @csrf
                                        <button type="submit" title="Activer"
                                                class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </form>
                                    @else
                                    <form method="POST" action="{{ route('gamification.close', $game->slug) }}" class="inline">
                                        @csrf
                                        <button type="submit" title="Clôturer"
                                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
