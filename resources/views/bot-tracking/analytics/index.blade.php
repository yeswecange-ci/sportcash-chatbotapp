@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Analytiques</h1>
                <p class="text-sm text-gray-500 mt-0.5">Statistiques detaillees du bot WhatsApp</p>
            </div>
            <a href="{{ route('bot-tracking.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">&larr; Dashboard</a>
        </div>

        {{-- Date Filter --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <form method="GET" action="{{ route('bot-tracking.statistics') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Date debut</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Date fin</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">Filtrer</button>
                <a href="{{ route('bot-tracking.statistics') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Reinitialiser</a>
            </form>
        </div>

        {{-- Stats Row 1 --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_conversations'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Conversations</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['active_conversations'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Actives</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['completed_conversations'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Completees</p>
            </div>
        </div>

        {{-- Stats Row 2 --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats['total_clients'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Clients</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-orange-500">{{ number_format($stats['total_non_clients'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Non-clients</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_messages'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Messages</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_events'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Evenements</p>
            </div>
        </div>

        {{-- Stats Row 3 --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats['total_menu_choices'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Choix menu</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_free_inputs'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Saisies libres</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['unique_clients'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Clients uniques</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['new_clients'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Nouveaux</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                @php $am = ($stats['avg_duration'] ?? 0) > 0 ? round(($stats['avg_duration'] ?? 0) / 60) : 0; @endphp
                <p class="text-2xl font-bold text-gray-900">{{ $am }}<span class="text-sm font-normal">min</span></p>
                <p class="text-xs text-gray-500 mt-0.5">Duree moy.</p>
            </div>
        </div>

        {{-- Charts Row 1: Menu + Status --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Repartition des menus</h3>
                <canvas id="menuDistributionChart" height="200"></canvas>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Statuts des conversations</h3>
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>

        {{-- Daily Trend --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Tendance quotidienne</h3>
            <canvas id="dailyTrendChart" height="80"></canvas>
        </div>

        {{-- Charts Row 2: Client + Peak Hours --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Clients vs Non-clients</h3>
                <canvas id="clientChart" height="200"></canvas>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Heures de pointe</h3>
                <canvas id="peakHoursChart" height="200"></canvas>
            </div>
        </div>

        {{-- Popular Paths --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Parcours populaires</h3>
            @if($popularPaths->isNotEmpty())
            <div class="space-y-2">
                @foreach($popularPaths as $path)
                @php
                    $steps = $path->menu_path;
                    if (is_string($steps)) {
                        $decoded = json_decode($steps, true);
                        $steps = is_array($decoded) ? $decoded : array_filter(explode(',', $steps));
                    }
                    $steps = is_array($steps) ? $steps : [];
                @endphp
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex flex-wrap items-center gap-1">
                        @forelse($steps as $step)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700">{{ $step }}</span>
                            @if(!$loop->last)<span class="text-gray-300 text-xs">&rarr;</span>@endif
                        @empty
                            <span class="text-xs text-gray-400 italic">Parcours non defini</span>
                        @endforelse
                    </div>
                    <span class="text-sm font-bold text-gray-900 ml-3">{{ $path->count ?? 0 }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-4">Aucune donnee disponible.</p>
            @endif
        </div>

        {{-- Event Breakdown + Widget Stats --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Types d'evenements</h3>
                @php $totalEvt = $eventStats->sum('count'); @endphp
                <div class="space-y-3">
                    @forelse($eventStats as $evt)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-600">{{ str_replace('_', ' ', $evt->event_type ?? '') }}</span>
                            <span class="font-medium text-gray-800">{{ number_format($evt->count ?? 0) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width:{{ $totalEvt > 0 ? round(($evt->count ?? 0) / $totalEvt * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-4">Aucun evenement.</p>
                    @endforelse
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Widgets utilises</h3>
                @php $totalWgt = $widgetStats->sum('count'); @endphp
                <div class="space-y-3">
                    @forelse($widgetStats as $wgt)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-600">{{ str_replace('_', ' ', $wgt->widget_name ?? '') }}</span>
                            <span class="font-medium text-gray-800">{{ number_format($wgt->count ?? 0) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-blue-500 h-1.5 rounded-full" style="width:{{ $totalWgt > 0 ? round(($wgt->count ?? 0) / $totalWgt * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-4">Aucun widget utilise.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Summary Table by status --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Resume par statut</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pourcentage</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @php
                            $totalForPct = array_sum($statusStats ?? []);
                            $statusBadge = [
                                'active'    => 'bg-green-100 text-green-700',
                                'completed' => 'bg-blue-100 text-blue-700',
                                'timeout'   => 'bg-amber-100 text-amber-700',
                                'abandoned' => 'bg-gray-100 text-gray-600',
                            ];
                        @endphp
                        @forelse($statusStats ?? [] as $status => $count)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[$status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ number_format($count) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $totalForPct > 0 ? round($count / $totalForPct * 100, 1) : 0 }}%</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-sm text-gray-400">Aucune donnee disponible.</td></tr>
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
    var menuPalette  = ['#e2001a','#0ea5e9','#10b981','#f59e0b','#8b5cf6','#ec4899','#64748b','#f97316','#06b6d4','#84cc16'];
    var statusColors = ['#10b981','#3b82f6','#8b5cf6','#f59e0b','#9ca3af'];

    // Menu Distribution Chart — dynamique
    var mCtx = document.getElementById('menuDistributionChart');
    if (mCtx) {
        var menuData = @json(
            collect($menuStats)->flatMap(function($choices, $menuName) {
                return collect($choices)->map(function($c) use ($menuName) {
                    return ['label' => $menuName . ' / ' . $c['label'], 'count' => $c['count']];
                });
            })->sortByDesc('count')->values()
        );
        new Chart(mCtx, {
            type: 'doughnut',
            data: {
                labels:   menuData.map(function(d){ return d.label; }),
                datasets: [{
                    data:            menuData.map(function(d){ return d.count; }),
                    backgroundColor: menuData.map(function(_,i){ return menuPalette[i % menuPalette.length]; }),
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'right' } }, cutout: '60%' }
        });
    }

    // Status Chart — $statusStats is ['active' => 5, 'completed' => 3, ...]
    var sCtx = document.getElementById('statusChart');
    if (sCtx) {
        new Chart(sCtx, {
            type: 'pie',
            data: {
                labels: @json(array_keys($statusStats ?? [])),
                datasets: [{
                    data: @json(array_values($statusStats ?? [])),
                    backgroundColor: statusColors,
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'right' } } }
        });
    }

    // Daily Trend Chart — column is total_conversations
    var dCtx = document.getElementById('dailyTrendChart');
    if (dCtx) {
        new Chart(dCtx, {
            type: 'line',
            data: {
                labels: @json($dailyStats->pluck('date')->map(fn($d) => (string)$d)->toArray()),
                datasets: [{
                    label: 'Conversations',
                    data: @json($dailyStats->pluck('total_conversations')->toArray()),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#6366f1'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // Client Chart
    var cCtx = document.getElementById('clientChart');
    if (cCtx) {
        new Chart(cCtx, {
            type: 'doughnut',
            data: {
                labels: ['Clients','Non-clients','Inconnus'],
                datasets: [{
                    data: [
                        {{ $stats['total_clients'] ?? 0 }},
                        {{ $stats['total_non_clients'] ?? 0 }},
                        {{ max(0, ($stats['total_conversations'] ?? 0) - ($stats['total_clients'] ?? 0) - ($stats['total_non_clients'] ?? 0)) }}
                    ],
                    backgroundColor: ['#6366f1','#f97316','#9ca3af'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'right' } }, cutout: '60%' }
        });
    }

    // Peak Hours Chart — $peakHours is a collection with ->hour and ->count
    var pCtx = document.getElementById('peakHoursChart');
    if (pCtx) {
        new Chart(pCtx, {
            type: 'bar',
            data: {
                labels: @json($peakHours->pluck('hour')->toArray()).map(function(h){ return h + 'h'; }),
                datasets: [{
                    label: 'Conversations',
                    data: @json($peakHours->pluck('count')->toArray()),
                    backgroundColor: 'rgba(99,102,241,0.7)',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
})();
</script>
@endpush
