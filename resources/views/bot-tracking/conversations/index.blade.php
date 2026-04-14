@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Bot Tracking &mdash; Dashboard</h1>
                <p class="text-sm text-gray-500 mt-0.5">Vue d'ensemble des conversations du bot WhatsApp</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('bot-tracking.active') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">Conversations actives</a>
                <a href="{{ route('bot-tracking.conversations') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Toutes les conversations</a>
            </div>
        </div>

        {{-- Date Filter --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <form method="GET" action="{{ route('bot-tracking.index') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Date debut</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Date fin</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">Filtrer</button>
                <a href="{{ route('bot-tracking.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Reinitialiser</a>
            </form>
        </div>

        {{-- Stat Cards Row 1 --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total conversations</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_conversations'] ?? 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">Sur la periode</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Actives</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ number_format($stats['active_conversations'] ?? 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">En cours maintenant</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Completees</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">{{ number_format($stats['completed_conversations'] ?? 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">Terminees</p>
            </div>
        </div>

        {{-- Stat Cards Row 2 --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Clients SportCash</p>
                <p class="text-3xl font-bold text-indigo-600 mt-1">{{ number_format($stats['total_clients'] ?? 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">Identifies</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Non-clients</p>
                <p class="text-3xl font-bold text-orange-500 mt-1">{{ number_format($stats['total_non_clients'] ?? 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">Prospects</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Duree moyenne</p>
                @php
                    $avgSec = $stats['avg_duration'] ?? 0;
                    $avgMin = $avgSec > 0 ? round($avgSec / 60) : 0;
                @endphp
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $avgMin }}<span class="text-base font-medium text-gray-500">min</span></p>
                <p class="text-xs text-gray-400 mt-1">Par conversation</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Evenements</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_events'] ?? 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ number_format($stats['total_messages'] ?? 0) }} messages</p>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Répartition des menus</h3>
                @php
                    $palette = ['#e2001a','#0ea5e9','#10b981','#f59e0b','#8b5cf6','#ec4899','#64748b','#f97316','#06b6d4','#84cc16'];
                    $colorIdx = 0;
                    // Flatten: un label par choix de menu (tous menus confondus)
                    $allChoices = collect();
                    foreach ($menuStats as $menuName => $choices) {
                        foreach ($choices as $c) {
                            $allChoices->push(['label' => $menuName . ' / ' . $c['label'], 'count' => $c['count']]);
                        }
                    }
                    $allChoices = $allChoices->sortByDesc('count')->values();
                @endphp
                @if($allChoices->isEmpty())
                    <p class="text-xs text-gray-400 text-center py-8">Aucune donnée de menu disponible.</p>
                @else
                    <div class="flex justify-center"><canvas id="menuChart" width="220" height="220"></canvas></div>
                    <div class="mt-4 space-y-1.5 max-h-40 overflow-y-auto">
                        @foreach($allChoices as $i => $item)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-3 h-3 rounded-full flex-shrink-0"
                                          style="background:{{ $palette[$i % count($palette)] }}"></span>
                                    <span class="text-gray-600 truncate max-w-[140px]">{{ $item['label'] }}</span>
                                </div>
                                <span class="font-medium text-gray-800">{{ $item['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Tendance quotidienne</h3>
                <canvas id="dailyChart" height="120"></canvas>
            </div>
        </div>

        {{-- Recent Conversations Table --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Conversations recentes</h3>
                <a href="{{ route('bot-tracking.conversations') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Voir tout &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Menu actuel</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duree</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Demarre</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentConversations as $conv)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <div class="font-medium">{{ $conv->display_name ?? 'Inconnu' }}</div>
                                <div class="text-xs text-gray-400">{{ $conv->phone_number }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusMap = ['active' => 'bg-green-100 text-green-700', 'completed' => 'bg-blue-100 text-blue-700', 'timeout' => 'bg-amber-100 text-amber-700', 'abandoned' => 'bg-gray-100 text-gray-600'];
                                    $sc = $statusMap[$conv->status] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">{{ ucfirst($conv->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($conv->is_client === true || $conv->is_client == 1)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Client</span>
                                @elseif($conv->is_client === false || $conv->is_client == 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Non-client</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inconnu</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $conv->current_menu ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($conv->duration_seconds)
                                    {{ round($conv->duration_seconds / 60) }}min
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($conv->started_at)
                                    {{ \Carbon\Carbon::parse($conv->started_at)->diffForHumans() }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('bot-tracking.conversations.show', $conv->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Voir &rarr;</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">Aucune conversation sur cette periode.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    var menuCtx = document.getElementById('menuChart');
    if (menuCtx) {
        var menuData = @json($allChoices->values());
        var palette  = ['#e2001a','#0ea5e9','#10b981','#f59e0b','#8b5cf6','#ec4899','#64748b','#f97316','#06b6d4','#84cc16'];
        new Chart(menuCtx, {
            type: 'doughnut',
            data: {
                labels:   menuData.map(function(d){ return d.label; }),
                datasets: [{
                    data:            menuData.map(function(d){ return d.count; }),
                    backgroundColor: menuData.map(function(_,i){ return palette[i % palette.length]; }),
                    borderWidth: 0, hoverOffset: 6
                }]
            },
            options: { responsive: false, plugins: { legend: { display: false } }, cutout: '65%' }
        });
    }
    var dailyCtx = document.getElementById('dailyChart');
    if (dailyCtx) {
        var labels = @json($dailyStats->pluck('date')->map(fn($d) => (string)$d)->toArray());
        var data   = @json($dailyStats->pluck('total_conversations')->toArray());
        new Chart(dailyCtx, { type: 'line', data: { labels: labels, datasets: [{ label: 'Conversations', data: data, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.08)', borderWidth: 2, pointRadius: 3, pointBackgroundColor: '#6366f1', fill: true, tension: 0.4 }] }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } } } });
    }
})();
</script>
@endpush
