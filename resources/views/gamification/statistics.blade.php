@extends('layouts.app')

@section('title', 'Statistiques — ' . $game->name)

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-start gap-3">
                <a href="{{ route('gamification.show', $game->slug) }}"
                   class="mt-1 w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-white hover:text-gray-600 hover:border hover:border-gray-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <nav class="flex items-center gap-1.5 text-xs text-gray-400 mb-0.5">
                        <a href="{{ route('gamification.index') }}" class="hover:text-gray-600 transition">Gamification</a>
                        <span>/</span>
                        <a href="{{ route('gamification.show', $game->slug) }}" class="hover:text-gray-600 transition">{{ $game->name }}</a>
                        <span>/</span>
                        <span class="text-gray-600">Statistiques</span>
                    </nav>
                    <h1 class="text-2xl font-bold text-gray-900">Statistiques</h1>
                    <div class="flex items-center gap-2 mt-0.5">
                        <p class="text-sm text-gray-500">{{ $game->name }}</p>
                        @php
                            $typeLabels = ['quiz' => 'Quiz', 'free_text' => 'Réponse libre', 'vote' => 'Vote', 'prediction' => 'Pronostic'];
                            $typeColors = ['quiz' => 'bg-violet-50 text-violet-700', 'free_text' => 'bg-blue-50 text-blue-700', 'vote' => 'bg-amber-50 text-amber-700', 'prediction' => 'bg-emerald-50 text-emerald-700'];
                        @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $typeColors[$game->type] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $typeLabels[$game->type] ?? $game->type }}
                        </span>
                    </div>
                </div>
            </div>
            <a href="{{ route('gamification.edit', $game->slug) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
        </div>

        @if(session('success'))
        <div class="mb-5 flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
            <svg class="w-4 h-4 flex-shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- KPIs --}}
        <div class="grid grid-cols-3 gap-4 mb-6">

            {{-- Taux de complétion --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 bg-violet-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $completionRate }}%</p>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Taux de complétion</p>
                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-3">
                    <div class="{{ $completionRate >= 70 ? 'bg-green-500' : ($completionRate >= 40 ? 'bg-amber-400' : 'bg-violet-500') }} h-1.5 rounded-full" style="width: {{ $completionRate }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1.5">{{ $completed }} / {{ $total }} participants</p>
            </div>

            {{-- Score moyen --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                @if($avgScore !== null)
                    <p class="text-3xl font-bold text-gray-900">{{ $avgScore }}%</p>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Score moyen</p>
                    <p class="text-xs text-gray-400 mt-3">de bonnes réponses (complétés)</p>
                @else
                    <p class="text-3xl font-bold text-gray-300">—</p>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Score moyen</p>
                    <p class="text-xs text-gray-400 mt-3">pas assez de données</p>
                @endif
            </div>

            {{-- Total participants --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $total }}</p>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mt-1">Participants</p>
                <div class="flex flex-wrap gap-x-3 gap-y-1 mt-3 text-xs">
                    <span class="flex items-center gap-1 text-green-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                        {{ $completed }} complétés
                    </span>
                    <span class="flex items-center gap-1 text-amber-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span>
                        {{ $started }} en cours
                    </span>
                    <span class="flex items-center gap-1 text-red-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 inline-block"></span>
                        {{ $abandoned }} abandonnés
                    </span>
                </div>
            </div>
        </div>

        {{-- Évolution temporelle --}}
        @if($timeline->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-7 h-7 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-100">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-semibold text-gray-800">Évolution des participations</h2>
                <span class="text-xs text-gray-400">{{ $timeline->count() }} jour(s)</span>
            </div>

            @php $maxCount = $timeline->max('count') ?: 1; @endphp
            <div class="flex items-end gap-1 overflow-x-auto pb-1" style="min-height: 80px">
                @foreach($timeline as $day)
                @php
                    $barHeight = max(4, round($day->count / $maxCount * 64));
                    $date = \Carbon\Carbon::parse($day->date);
                @endphp
                <div class="flex flex-col items-center gap-1 flex-shrink-0" style="min-width: 36px"
                     title="{{ $date->format('d/m/Y') }} : {{ $day->count }} participation(s)">
                    <span class="text-xs font-semibold text-gray-600">{{ $day->count }}</span>
                    <div class="w-full rounded-t-md bg-violet-500 hover:bg-violet-600 transition"
                         style="height: {{ $barHeight }}px; width: 28px"></div>
                    <span class="text-xs text-gray-400" style="font-size: 10px">{{ $date->format('d/m') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Funnel --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-7 h-7 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-100">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-semibold text-gray-800">Entonnoir de participation</h2>
            </div>

            @php
                $funnelItems = [
                    ['label' => 'Démarré',                'value' => $funnel['started'],            'color' => 'bg-violet-500', 'text' => 'text-violet-600'],
                    ['label' => 'Complété',               'value' => $funnel['completed'],           'color' => 'bg-green-500',  'text' => 'text-green-600'],
                    ['label' => 'Au moins 1 bonne rép.',  'value' => $funnel['at_least_1_correct'],  'color' => 'bg-blue-500',   'text' => 'text-blue-600'],
                ];
                $maxVal = max(array_column($funnelItems, 'value')) ?: 1;
            @endphp

            <div class="flex items-end gap-0">
                @foreach($funnelItems as $fi => $item)
                <div class="flex-1 {{ $fi > 0 ? 'ml-1' : '' }}">
                    <div class="flex items-baseline justify-between mb-2">
                        <p class="text-xs text-gray-500">{{ $item['label'] }}</p>
                        @if($fi > 0 && $funnel['started'] > 0)
                        <p class="text-xs font-medium {{ $item['text'] }}">{{ round($item['value'] / $funnel['started'] * 100) }}%</p>
                        @endif
                    </div>
                    <div class="relative h-10 bg-gray-100 rounded-lg overflow-hidden">
                        <div class="{{ $item['color'] }} h-full rounded-lg"
                             style="width: {{ $maxVal > 0 ? round($item['value'] / $maxVal * 100) : 0 }}%"></div>
                    </div>
                    <p class="mt-1.5 text-xl font-bold text-gray-900">{{ $item['value'] }}</p>
                </div>
                @if($fi < count($funnelItems) - 1)
                <div class="flex items-center pb-5 px-1 text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                @endif
                @endforeach
            </div>
        </div>

        {{-- Analyse par question --}}
        @if(!empty($questionStats))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-7 h-7 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-100">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-semibold text-gray-800">Analyse par question</h2>
                <span class="text-xs text-gray-400">{{ count($questionStats) }} question(s)</span>
            </div>

            <div class="space-y-4">
                @foreach($questionStats as $qs)
                @php
                    $question = $qs['question'];
                    $typeLabelsQ = ['mcq' => 'QCM', 'free_text' => 'Réponse libre', 'vote' => 'Vote', 'prediction' => 'Pronostic'];
                    $needsManualReview = in_array($question->type, ['free_text', 'prediction']) && $qs['null_count'] > 0;
                @endphp
                <div class="border border-gray-100 rounded-xl p-4 {{ $needsManualReview ? 'border-amber-200 bg-amber-50/30' : 'bg-gray-50/50' }}">

                    {{-- En-tête question --}}
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-violet-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-violet-700">{{ $question->order }}</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                    <span class="text-xs px-1.5 py-0.5 bg-white border border-gray-200 text-gray-500 rounded font-medium">
                                        {{ $typeLabelsQ[$question->type] ?? $question->type }}
                                    </span>
                                    @if($question->correct_answer)
                                    <span class="text-xs px-1.5 py-0.5 bg-green-50 border border-green-200 text-green-700 rounded">
                                        ✓ {{ $question->correct_answer }}
                                    </span>
                                    @endif
                                    @if($needsManualReview)
                                    <span class="text-xs px-1.5 py-0.5 bg-amber-100 border border-amber-200 text-amber-700 rounded font-medium">
                                        ⚠ {{ $qs['null_count'] }} à valider
                                    </span>
                                    @endif
                                </div>
                                <p class="text-sm font-medium text-gray-800">{{ $question->text }}</p>
                            </div>
                        </div>

                        @if($qs['total'] > 0)
                        <div class="text-right flex-shrink-0">
                            @if($qs['rate'] !== null)
                                <p class="text-2xl font-bold {{ $qs['rate'] >= 70 ? 'text-green-600' : ($qs['rate'] >= 40 ? 'text-amber-500' : 'text-red-600') }}">
                                    {{ $qs['rate'] }}%
                                </p>
                                <p class="text-xs text-gray-400">corrects</p>
                            @else
                                <p class="text-2xl font-bold text-gray-700">{{ $qs['total'] }}</p>
                                <p class="text-xs text-gray-400">réponse(s)</p>
                            @endif
                        </div>
                        @endif
                    </div>

                    @if($qs['total'] === 0)
                    <p class="text-xs text-gray-400 pl-10">Aucune réponse pour cette question.</p>
                    @else

                    {{-- Barre corrects/incorrects --}}
                    @if($qs['rate'] !== null)
                    <div class="mb-3 pl-10">
                        <div class="flex gap-px h-2.5 rounded-full overflow-hidden bg-gray-200">
                            @if($qs['correct'] > 0)
                            <div class="bg-green-500 h-full" style="width: {{ round($qs['correct'] / $qs['total'] * 100) }}%"></div>
                            @endif
                            @if($qs['wrong'] > 0)
                            <div class="bg-red-400 h-full" style="width: {{ round($qs['wrong'] / $qs['total'] * 100) }}%"></div>
                            @endif
                            @if($qs['null_count'] > 0)
                            <div class="bg-gray-300 h-full" style="width: {{ round($qs['null_count'] / $qs['total'] * 100) }}%"></div>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs">
                            <span class="flex items-center gap-1.5 text-green-700">
                                <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                                {{ $qs['correct'] }} corrects
                            </span>
                            <span class="flex items-center gap-1.5 text-red-600">
                                <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>
                                {{ $qs['wrong'] }} incorrects
                            </span>
                            @if($qs['null_count'] > 0)
                            <span class="flex items-center gap-1.5 text-gray-400">
                                <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>
                                {{ $qs['null_count'] }} non évalués
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Zone de validation manuelle pour free_text et prediction --}}
                    @if($needsManualReview)
                    <div class="pl-10 mb-3">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <p class="text-xs font-semibold text-amber-800 mb-2.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Réponses à valider manuellement
                            </p>
                            <div class="space-y-1.5">
                                @foreach($question->answers->whereNull('is_correct') as $answer)
                                <div class="flex items-center gap-2 bg-white rounded-lg px-3 py-2 border border-amber-100">
                                    <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-gray-500">
                                            {{ strtoupper(substr($answer->participation->participant_name ?? '?', 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-700 flex-1 min-w-0 truncate"
                                          title="{{ $answer->answer_text }}">{{ $answer->answer_text }}</span>
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        <form method="POST" action="{{ route('gamification.answers.mark', [$game->slug, $answer->id]) }}">
                                            @csrf
                                            <input type="hidden" name="is_correct" value="1">
                                            <button type="submit"
                                                    class="w-7 h-7 flex items-center justify-center bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition font-bold text-sm"
                                                    title="Marquer comme correct">✓</button>
                                        </form>
                                        <form method="POST" action="{{ route('gamification.answers.mark', [$game->slug, $answer->id]) }}">
                                            @csrf
                                            <input type="hidden" name="is_correct" value="0">
                                            <button type="submit"
                                                    class="w-7 h-7 flex items-center justify-center bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition font-bold text-sm"
                                                    title="Marquer comme incorrect">✗</button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Distribution des réponses --}}
                    @if(!empty($qs['distribution']))
                    <div class="pl-10">
                        <p class="text-xs font-medium text-gray-500 mb-2">Distribution des réponses</p>
                        @php $maxDist = max(array_column($qs['distribution'], 'count')) ?: 1; @endphp
                        <div class="space-y-1.5">
                            @foreach(array_slice($qs['distribution'], 0, 8) as $dist)
                            <div class="flex items-center gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="text-xs text-gray-600 truncate">{{ $dist['text'] }}</span>
                                        <span class="text-xs font-semibold text-gray-700 ml-2 flex-shrink-0">{{ $dist['count'] }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded h-1.5">
                                        <div class="bg-violet-400 h-1.5 rounded" style="width: {{ round($dist['count'] / $maxDist * 100) }}%"></div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400 flex-shrink-0 w-8 text-right">
                                    {{ $qs['total'] > 0 ? round($dist['count'] / $qs['total'] * 100) : 0 }}%
                                </span>
                            </div>
                            @endforeach
                            @if(count($qs['distribution']) > 8)
                            <p class="text-xs text-gray-400 mt-1">+ {{ count($qs['distribution']) - 8 }} autres réponses</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    @endif {{-- end if total > 0 --}}
                </div>
                @endforeach
            </div>
        </div>

        @else
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center py-14">
            <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500">Ce jeu n'a pas encore de questions.</p>
            <a href="{{ route('gamification.edit', $game->slug) }}"
               class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-violet-700 bg-violet-50 border border-violet-200 rounded-lg hover:bg-violet-100 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter des questions
            </a>
        </div>
        @endif

    </div>
</div>
@endsection
