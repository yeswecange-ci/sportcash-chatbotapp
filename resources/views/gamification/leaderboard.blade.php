@extends('layouts.app')

@section('title', 'Classement — ' . $game->name)

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">

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
                        <span class="text-gray-600">Classement</span>
                    </nav>
                    <h1 class="text-2xl font-bold text-gray-900">Classement</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $game->name }} · {{ $rankings->count() }} participant(s) classé(s)</p>
                </div>
            </div>

            @if($rankings->isNotEmpty())
            @php $topScore = $rankings->first()['score']; @endphp
            @php $topCount = $rankings->filter(fn($r) => $r['score'] === $topScore)->count(); @endphp
            @if($topCount > 1)
            <button onclick="randomDraw()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Tirage parmi les {{ $topCount }} ex-æquo
            </button>
            @endif
            @endif
        </div>

        @if(session('success'))
        <div class="mb-5 flex items-center gap-2.5 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
            <svg class="w-4 h-4 flex-shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if($rankings->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center py-16">
            <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500">Aucun participant n'a encore complété ce jeu.</p>
            <a href="{{ route('gamification.show', $game->slug) }}"
               class="mt-3 inline-block text-xs text-violet-600 hover:underline">
                Retour au jeu
            </a>
        </div>

        @else

        {{-- Podium top 3 --}}
        @if($rankings->count() >= 2)
        <div class="flex items-end justify-center gap-3 mb-8">
            @php
                $podium = [];
                if ($rankings->count() >= 2) $podium[1] = $rankings[1]; // 2e
                $podium[0] = $rankings[0]; // 1er
                if ($rankings->count() >= 3) $podium[2] = $rankings[2]; // 3e
            @endphp

            {{-- 2e place --}}
            @if(isset($podium[1]))
            <div class="flex flex-col items-center gap-2 w-28">
                <div class="w-12 h-12 rounded-full bg-gray-100 border-2 border-gray-300 flex items-center justify-center text-base font-bold text-gray-600">
                    {{ strtoupper(substr($podium[1]['participation']->participant_name ?? '?', 0, 1)) }}
                </div>
                <p class="text-xs font-medium text-gray-600 text-center truncate w-full">{{ $podium[1]['participation']->participant_name ?? '—' }}</p>
                <p class="text-sm font-bold text-gray-500">{{ $podium[1]['score'] }}%</p>
                <div class="w-full bg-gray-200 rounded-t-lg flex items-center justify-center text-gray-500 font-bold text-lg" style="height: 60px">2</div>
            </div>
            @endif

            {{-- 1re place --}}
            <div class="flex flex-col items-center gap-2 w-28">
                <div class="text-2xl">🏆</div>
                <div class="w-14 h-14 rounded-full bg-amber-100 border-2 border-amber-400 flex items-center justify-center text-lg font-bold text-amber-700">
                    {{ strtoupper(substr($podium[0]['participation']->participant_name ?? '?', 0, 1)) }}
                </div>
                <p class="text-xs font-semibold text-gray-800 text-center truncate w-full">{{ $podium[0]['participation']->participant_name ?? '—' }}</p>
                <p class="text-sm font-bold text-amber-600">{{ $podium[0]['score'] }}%</p>
                <div class="w-full bg-amber-400 rounded-t-lg flex items-center justify-center text-white font-bold text-xl" style="height: 80px">1</div>
            </div>

            {{-- 3e place --}}
            @if(isset($podium[2]))
            <div class="flex flex-col items-center gap-2 w-28">
                <div class="w-12 h-12 rounded-full bg-orange-50 border-2 border-orange-200 flex items-center justify-center text-base font-bold text-orange-600">
                    {{ strtoupper(substr($podium[2]['participation']->participant_name ?? '?', 0, 1)) }}
                </div>
                <p class="text-xs font-medium text-gray-600 text-center truncate w-full">{{ $podium[2]['participation']->participant_name ?? '—' }}</p>
                <p class="text-sm font-bold text-orange-500">{{ $podium[2]['score'] }}%</p>
                <div class="w-full bg-orange-200 rounded-t-lg flex items-center justify-center text-orange-700 font-bold text-lg" style="height: 44px">3</div>
            </div>
            @endif
        </div>
        @endif

        {{-- Tableau complet --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100" id="leaderboard-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">#</th>
                        <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participant</th>
                        <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temps</th>
                        <th class="px-4 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terminé le</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php $prevScore = null; $displayRank = 0; $realRank = 0; @endphp
                    @foreach($rankings as $entry)
                    @php
                        $realRank++;
                        if ($entry['score'] !== $prevScore) {
                            $displayRank = $realRank;
                        }
                        $prevScore = $entry['score'];

                        $p = $entry['participation'];
                        $isTop = $displayRank === 1;
                        $duration = $p->started_at && $p->completed_at
                            ? $p->started_at->diffForHumans($p->completed_at, true)
                            : '—';

                        $rankColors = [
                            1 => 'bg-amber-50',
                            2 => 'bg-gray-50',
                            3 => 'bg-orange-50/50',
                        ];
                        $rowClass = $rankColors[$displayRank] ?? '';
                    @endphp
                    <tr class="hover:bg-violet-50/30 transition {{ $rowClass }} leaderboard-row"
                        data-rank="{{ $displayRank }}"
                        data-score="{{ $entry['score'] }}">
                        <td class="px-4 py-3.5">
                            @if($displayRank === 1)
                                <span class="text-lg">🥇</span>
                            @elseif($displayRank === 2)
                                <span class="text-lg">🥈</span>
                            @elseif($displayRank === 3)
                                <span class="text-lg">🥉</span>
                            @else
                                <span class="text-sm font-medium text-gray-400">{{ $displayRank }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                    {{ $isTop ? 'bg-amber-100' : 'bg-violet-100' }}">
                                    <span class="text-xs font-bold {{ $isTop ? 'text-amber-700' : 'text-violet-600' }}">
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
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm font-bold {{ $entry['score'] >= 80 ? 'text-green-600' : ($entry['score'] >= 50 ? 'text-amber-500' : 'text-red-500') }}">
                                    {{ $entry['score'] }}%
                                </span>
                                <span class="text-xs text-gray-400">{{ $entry['correct'] }}/{{ $entry['total'] }}</span>
                                <div class="w-16 bg-gray-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $entry['score'] >= 80 ? 'bg-green-500' : ($entry['score'] >= 50 ? 'bg-amber-400' : 'bg-red-400') }}"
                                         style="width: {{ $entry['score'] }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-500">
                            {{ $duration }}
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-400">
                            {{ $p->completed_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @endif

    </div>
</div>

{{-- Overlay tirage au sort --}}
<div id="draw-overlay" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center">
        <div class="text-4xl mb-3" id="draw-emoji">🎲</div>
        <h2 class="text-lg font-bold text-gray-900 mb-1" id="draw-title">Tirage en cours...</h2>
        <p class="text-sm text-gray-500 mb-6" id="draw-subtitle">Sélection d'un gagnant parmi les ex-æquo</p>

        <div id="draw-result" class="hidden mb-6">
            <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-3 text-2xl font-bold text-amber-700" id="draw-avatar">?</div>
            <p class="text-xl font-bold text-gray-900" id="draw-name">—</p>
            <p class="text-sm text-gray-400 font-mono" id="draw-phone">—</p>
        </div>

        <button onclick="closeDraw()"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-violet-600 rounded-lg hover:bg-violet-700 transition">
            Fermer
        </button>
    </div>
</div>

@push('scripts')
<script>
// Données des ex-æquo (score maximal)
const topScore = {{ $rankings->isNotEmpty() ? $rankings->first()['score'] : 0 }};
const topParticipants = [
    @foreach($rankings->filter(fn($r) => $r['score'] === ($rankings->isNotEmpty() ? $rankings->first()['score'] : -1)) as $entry)
    {
        name: @json($entry['participation']->participant_name ?? '—'),
        phone: @json($entry['participation']->phone_number),
    },
    @endforeach
];

function randomDraw() {
    if (topParticipants.length === 0) return;

    const overlay = document.getElementById('draw-overlay');
    const result  = document.getElementById('draw-result');
    const title   = document.getElementById('draw-title');
    const emoji   = document.getElementById('draw-emoji');

    overlay.classList.remove('hidden');
    result.classList.add('hidden');
    title.textContent = 'Tirage en cours...';
    emoji.textContent = '🎲';

    // Animation de 1.5s puis affichage du gagnant
    let count = 0;
    const interval = setInterval(function() {
        const random = topParticipants[Math.floor(Math.random() * topParticipants.length)];
        document.getElementById('draw-avatar').textContent = random.name.charAt(0).toUpperCase();
        document.getElementById('draw-name').textContent   = random.name;
        document.getElementById('draw-phone').textContent  = random.phone;
        result.classList.remove('hidden');
        count++;
        if (count >= 12) {
            clearInterval(interval);
            emoji.textContent = '🏆';
            title.textContent = 'Gagnant(e) !';
        }
    }, 120);
}

function closeDraw() {
    document.getElementById('draw-overlay').classList.add('hidden');
}
</script>
@endpush
@endsection
