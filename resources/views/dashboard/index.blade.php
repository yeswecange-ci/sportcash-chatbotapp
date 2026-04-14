@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Vue d'ensemble de l'activite support
                    @if($lastSynced)
                        &nbsp;·&nbsp;<span class="text-xs text-gray-400" id="last-synced-label" title="{{ $lastSynced->format('d/m/Y H:i:s') }}">
                            Synchro : {{ $lastSynced->diffForHumans() }}
                        </span>
                    @else
                        &nbsp;·&nbsp;<span class="text-xs text-amber-500" id="last-synced-label">Jamais synchronise</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Bouton synchronisation manuelle --}}
                <button id="sync-btn" onclick="syncStats(this)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-600 text-xs font-medium rounded-lg hover:bg-indigo-100 border border-indigo-200 transition"
                    title="Synchroniser les stats depuis Chatwoot">
                    <svg id="sync-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <svg id="sync-spinner" class="w-3.5 h-3.5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <span id="sync-label">Synchroniser</span>
                </button>
            <div class="flex gap-1 bg-gray-100 rounded-lg p-0.5" id="period-selector">
                @foreach(['today' => "Aujourd'hui", 'week' => 'Semaine', 'month' => 'Mois', 'quarter' => 'Trimestre'] as $p => $label)
                    <button onclick="changePeriod('{{ $p }}')"
                       data-period="{{ $p }}"
                       class="period-btn px-3 py-1.5 text-xs font-medium rounded-md transition
                              {{ $period === $p ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            </div>
        </div>
    </div>

    @php
        $summary = $stats['summary'] ?? [];
        $convCount = $summary['conversations_count'] ?? 0;
        $resolutions = $summary['resolutions_count'] ?? 0;
        $avgFirstResp = $summary['avg_first_response_time'] ?? 0;
        $avgResolution = $summary['avg_resolution_time'] ?? 0;
        $incomingMsgs = $summary['incoming_messages_count'] ?? 0;
        $outgoingMsgs = $summary['outgoing_messages_count'] ?? 0;
        $resolutionRate = $convCount > 0 ? round(($resolutions / $convCount) * 100) : 0;

        $fmtTime = function($seconds) {
            if (!$seconds || $seconds == 0) return '-';
            $s = (int) $seconds;
            if ($s < 60) return $s . 's';
            if ($s < 3600) return round($s / 60) . 'min';
            return round($s / 3600, 1) . 'h';
        };
    @endphp

    <div class="p-6 space-y-6">

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4" id="kpi-cards">
            {{-- Conversations --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Conversations</p>
                </div>
                <p class="text-2xl font-bold text-gray-900" id="kpi-conversations">{{ $convCount }}</p>
            </div>

            {{-- Resolues --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Resolues</p>
                </div>
                <p class="text-2xl font-bold text-green-600" id="kpi-resolutions">{{ $resolutions }}</p>
            </div>

            {{-- 1ere reponse --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">1ere reponse</p>
                </div>
                <p class="text-2xl font-bold text-blue-600" id="kpi-first-response">{{ $fmtTime($avgFirstResp) }}</p>
            </div>

            {{-- Resolution moy --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Resolution moy.</p>
                </div>
                <p class="text-2xl font-bold text-purple-600" id="kpi-resolution-time">{{ $fmtTime($avgResolution) }}</p>
            </div>

            {{-- Taux resolution --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Taux resolution</p>
                </div>
                <p class="text-2xl font-bold text-amber-600" id="kpi-resolution-rate">{{ $resolutionRate }}%</p>
            </div>

            {{-- Messages --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Messages</p>
                </div>
                <p class="text-2xl font-bold text-gray-900" id="kpi-messages">{{ $incomingMsgs + $outgoingMsgs }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5" id="kpi-messages-detail">{{ $incomingMsgs }} entrants / {{ $outgoingMsgs }} sortants</p>
            </div>
        </div>

        {{-- Charts Row 1: Conversation Trends + Distribution --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Conversation Trends (Line Chart) --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Tendance des conversations</h3>
                    <div class="flex items-center gap-4 text-[10px]">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Conversations</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Resolues</span>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="convTrendChart"></canvas>
                </div>
            </div>

            {{-- Conversation Distribution (Doughnut) --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Repartition des conversations</h3>
                <div class="h-52 flex items-center justify-center">
                    <canvas id="convDistChart"></canvas>
                </div>
                <div class="mt-4 space-y-2" id="conv-dist-legend">
                    @php
                        $distItems = [
                            ['label' => 'Mes conversations', 'count' => $counts['mine_count'] ?? 0, 'color' => '#6366f1'],
                            ['label' => 'Non assignees', 'count' => $counts['unassigned_count'] ?? 0, 'color' => '#f97316'],
                            ['label' => 'Assignees (autres)', 'count' => max(0, ($counts['assigned_count'] ?? 0) - ($counts['mine_count'] ?? 0)), 'color' => '#3b82f6'],
                            ['label' => 'En attente', 'count' => $counts['pending_count'] ?? 0, 'color' => '#eab308'],
                        ];
                    @endphp
                    @foreach($distItems as $item)
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background: {{ $item['color'] }}"></span>
                                <span class="text-gray-600">{{ $item['label'] }}</span>
                            </span>
                            <span class="font-semibold text-gray-900">{{ $item['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Charts Row 2: Messages Trends + Agent Performance --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Message Trends (Bar Chart) --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Volume de messages</h3>
                    <div class="flex items-center gap-4 text-[10px]">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> Entrants</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span> Sortants</span>
                    </div>
                </div>
                <div class="h-56">
                    <canvas id="msgTrendChart"></canvas>
                </div>
            </div>

            {{-- Agent Performance (Horizontal Bar) --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Performance des agents</h3>
                <div class="h-56">
                    <canvas id="agentChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Agent Leaderboard Table --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Classement des agents</h3>
            <div class="overflow-x-auto">
                <table class="w-full" id="agent-table">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-2 px-3 text-[10px] font-semibold text-gray-400 uppercase">#</th>
                            <th class="text-left py-2 px-3 text-[10px] font-semibold text-gray-400 uppercase">Agent</th>
                            <th class="text-right py-2 px-3 text-[10px] font-semibold text-gray-400 uppercase">Conversations</th>
                            <th class="text-right py-2 px-3 text-[10px] font-semibold text-gray-400 uppercase">Resolues</th>
                            <th class="text-right py-2 px-3 text-[10px] font-semibold text-gray-400 uppercase">1ere reponse</th>
                            <th class="text-right py-2 px-3 text-[10px] font-semibold text-gray-400 uppercase">Resolution moy.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="agent-tbody">
                        @php
                            $leaderboard = is_array($agentStats) ? $agentStats : [];
                            $agentMap = collect($agents)->keyBy('id');
                        @endphp
                        @forelse($leaderboard as $i => $entry)
                            @php
                                $agentId = $entry['id'] ?? null;
                                $agentInfo = $agentId ? ($agentMap[$agentId] ?? null) : null;
                                $agentName = $agentInfo['name'] ?? ('Agent #' . $agentId);
                                $convs = $entry['metric']['conversations_count'] ?? 0;
                                $resolved = $entry['metric']['resolutions_count'] ?? 0;
                                $firstResp = $entry['metric']['avg_first_response_time'] ?? 0;
                                $resMoy = $entry['metric']['avg_resolution_time'] ?? 0;
                                $avatarColors = ['bg-indigo-100 text-indigo-600', 'bg-green-100 text-green-600', 'bg-blue-100 text-blue-600', 'bg-amber-100 text-amber-600', 'bg-rose-100 text-rose-600'];
                                $avatarColor = $avatarColors[$i % count($avatarColors)];
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-2.5 px-3 text-xs text-gray-400 font-mono">{{ $i + 1 }}</td>
                                <td class="py-2.5 px-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full {{ $avatarColor }} flex items-center justify-center font-semibold text-[10px]">
                                            {{ mb_substr($agentName, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $agentName }}</span>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 text-right text-sm font-semibold text-gray-900">{{ $convs }}</td>
                                <td class="py-2.5 px-3 text-right text-sm text-green-600 font-medium">{{ $resolved }}</td>
                                <td class="py-2.5 px-3 text-right text-sm text-gray-600">{{ $fmtTime($firstResp) }}</td>
                                <td class="py-2.5 px-3 text-right text-sm text-gray-600">{{ $fmtTime($resMoy) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-gray-400">Aucune donnee disponible pour cette periode</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('conversations.index', ['status' => 'open', 'assignee_type' => 'unassigned']) }}"
               class="bg-white rounded-xl p-5 border border-gray-200 hover:border-orange-300 hover:shadow-md transition group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm">Non assignees</h3>
                        <p class="text-xs text-gray-500 mt-0.5"><span class="font-bold text-orange-600" id="quick-unassigned">{{ $counts['unassigned_count'] ?? 0 }}</span> en attente</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('conversations.index', ['status' => 'open']) }}"
               class="bg-white rounded-xl p-5 border border-gray-200 hover:border-indigo-300 hover:shadow-md transition group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm">Conversations ouvertes</h3>
                        <p class="text-xs text-gray-500 mt-0.5"><span class="font-bold text-indigo-600" id="quick-open">{{ $counts['all_count'] ?? 0 }}</span> en cours</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('conversations.index', ['status' => 'pending']) }}"
               class="bg-white rounded-xl p-5 border border-gray-200 hover:border-yellow-300 hover:shadow-md transition group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200 transition">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm">En attente</h3>
                        <p class="text-xs text-gray-500 mt-0.5"><span class="font-bold text-yellow-600" id="quick-pending">{{ $counts['pending_count'] ?? 0 }}</span> conversations</p>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

@push('scripts')
<script>
(function() {
    const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

    // ═══ Chart.js defaults ═══
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#9ca3af';
    Chart.defaults.plugins.legend.display = false;

    // ═══ Plugin : message "Aucune donnée" quand le chart est vide ═══
    const noDataPlugin = {
        id: 'noData',
        afterDraw(chart) {
            const hasData = chart.data.datasets.some(ds => {
                if (!Array.isArray(ds.data) || ds.data.length === 0) return false;
                return ds.data.some(v => v !== null && v !== undefined && Number(v) > 0);
            });
            if (hasData) return;

            const { ctx, chartArea: a } = chart;
            if (!a) return;
            ctx.save();
            const cx = a.left + (a.right - a.left) / 2;
            const cy = a.top  + (a.bottom - a.top) / 2;

            // Fond léger
            ctx.fillStyle = 'rgba(249,250,251,0.85)';
            ctx.fillRect(a.left, a.top, a.right - a.left, a.bottom - a.top);

            // Icône cercle barré
            ctx.strokeStyle = '#d1d5db';
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.arc(cx, cy - 18, 14, 0, Math.PI * 2);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(cx - 10, cy - 18 - 10);
            ctx.lineTo(cx + 10, cy - 18 + 10);
            ctx.stroke();

            // Texte
            ctx.fillStyle = '#9ca3af';
            ctx.font = '12px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('Aucune donnée disponible', cx, cy + 8);
            ctx.font = '10px Inter, sans-serif';
            ctx.fillStyle = '#d1d5db';
            ctx.fillText('Synchronisez les données pour afficher ce graphique', cx, cy + 26);
            ctx.restore();
        },
    };
    Chart.register(noDataPlugin);

    // ═══ Helper: format timestamps to labels ═══
    function formatTimestamps(data) {
        if (!Array.isArray(data)) return { labels: [], values: [] };
        return {
            labels: data.map(d => {
                if (!d.timestamp) return '';
                const date = new Date(d.timestamp * 1000);
                return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
            }),
            values: data.map(d => d.value || 0),
        };
    }

    function fmtTime(seconds) {
        if (!seconds || seconds === 0) return '-';
        const s = Math.round(seconds);
        if (s < 60) return s + 's';
        if (s < 3600) return Math.round(s / 60) + 'min';
        return (s / 3600).toFixed(1) + 'h';
    }

    // ═══ Initial data from Blade ═══
    const initConvTrend = @json($stats['conversations_count'] ?? []);
    const initResolutions = @json($stats['resolutions'] ?? []);
    const initIncoming = @json($stats['incoming_messages'] ?? []);
    const initOutgoing = @json($stats['outgoing_messages'] ?? []);
    const initAgentStats = @json($agentStats ?? []);
    const initAgents = @json($agents ?? []);
    const initCounts = @json($counts ?? []);

    // ═══ 1. Conversation Trends Line Chart ═══
    const convCtx = document.getElementById('convTrendChart').getContext('2d');
    const convData = formatTimestamps(initConvTrend);
    const resData = formatTimestamps(initResolutions);

    const convTrendChart = new Chart(convCtx, {
        type: 'line',
        data: {
            labels: convData.labels,
            datasets: [
                {
                    label: 'Conversations',
                    data: convData.values,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#6366f1',
                },
                {
                    label: 'Resolues',
                    data: resData.values,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#22c55e',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { maxTicksLimit: 10 },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { precision: 0 },
                },
            },
            plugins: {
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleFont: { size: 12 },
                    bodyFont: { size: 11 },
                    padding: 10,
                    cornerRadius: 8,
                },
            },
        },
    });

    // ═══ 2. Conversation Distribution Doughnut ═══
    const distCtx = document.getElementById('convDistChart').getContext('2d');
    const distData = [
        initCounts.mine_count || 0,
        initCounts.unassigned_count || 0,
        Math.max(0, (initCounts.assigned_count || 0) - (initCounts.mine_count || 0)),
        initCounts.pending_count || 0,
    ];

    const convDistChart = new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: ['Mes conversations', 'Non assignees', 'Assignees (autres)', 'En attente'],
            datasets: [{
                data: distData,
                backgroundColor: ['#6366f1', '#f97316', '#3b82f6', '#eab308'],
                borderWidth: 0,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                tooltip: {
                    backgroundColor: '#1f2937',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                            return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        },
                    },
                },
            },
        },
    });

    // ═══ 3. Message Volume Bar Chart ═══
    const msgCtx = document.getElementById('msgTrendChart').getContext('2d');
    const inData = formatTimestamps(initIncoming);
    const outData = formatTimestamps(initOutgoing);

    const msgTrendChart = new Chart(msgCtx, {
        type: 'bar',
        data: {
            labels: inData.labels.length ? inData.labels : outData.labels,
            datasets: [
                {
                    label: 'Entrants',
                    data: inData.values,
                    backgroundColor: 'rgba(14, 165, 233, 0.7)',
                    borderRadius: 4,
                    barPercentage: 0.6,
                },
                {
                    label: 'Sortants',
                    data: outData.values,
                    backgroundColor: 'rgba(139, 92, 246, 0.7)',
                    borderRadius: 4,
                    barPercentage: 0.6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { maxTicksLimit: 10 },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { precision: 0 },
                },
            },
            plugins: {
                tooltip: {
                    backgroundColor: '#1f2937',
                    padding: 10,
                    cornerRadius: 8,
                },
            },
        },
    });

    // ═══ 4. Agent Performance Horizontal Bar ═══
    const agentCtx = document.getElementById('agentChart').getContext('2d');
    const agentMap = {};
    (initAgents || []).forEach(a => { agentMap[a.id] = a; });

    function buildAgentChartData(stats) {
        const entries = Array.isArray(stats) ? stats : [];
        const names = entries.map(e => {
            const info = agentMap[e.id];
            return info ? info.name : ('Agent #' + e.id);
        });
        const convs = entries.map(e => e.metric?.conversations_count || 0);
        const resolved = entries.map(e => e.metric?.resolutions_count || 0);
        return { names, convs, resolved };
    }

    const agentData = buildAgentChartData(initAgentStats);

    const agentChart = new Chart(agentCtx, {
        type: 'bar',
        data: {
            labels: agentData.names,
            datasets: [
                {
                    label: 'Conversations',
                    data: agentData.convs,
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderRadius: 4,
                    barPercentage: 0.5,
                },
                {
                    label: 'Resolues',
                    data: agentData.resolved,
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderRadius: 4,
                    barPercentage: 0.5,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { precision: 0 },
                },
                y: {
                    grid: { display: false },
                },
            },
            plugins: {
                tooltip: {
                    backgroundColor: '#1f2937',
                    padding: 10,
                    cornerRadius: 8,
                },
            },
        },
    });

    // ═══ Sync Stats ═══
    window.syncStats = async function(btn) {
        const icon = document.getElementById('sync-icon');
        const spinner = document.getElementById('sync-spinner');
        const label = document.getElementById('sync-label');
        btn.disabled = true;
        icon.classList.add('hidden');
        spinner.classList.remove('hidden');
        label.textContent = 'Sync...';

        try {
            const r = await fetch('{{ route("ajax.dashboard.sync") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ period: 'all' }),
            });
            const res = await r.json();
            if (res.success) {
                label.textContent = 'Synchroniser';
                const syncLabel = document.getElementById('last-synced-label');
                if (syncLabel && res.lastSynced) {
                    const d = new Date(res.lastSynced);
                    syncLabel.textContent = 'Synchro : a l\'instant';
                    syncLabel.classList.remove('text-amber-500');
                    syncLabel.classList.add('text-gray-400');
                }
                // Recharger les donnees du tableau de bord
                await changePeriod(currentPeriod, true);
            } else {
                label.textContent = 'Erreur';
                setTimeout(() => { label.textContent = 'Synchroniser'; }, 3000);
            }
        } catch(e) {
            label.textContent = 'Erreur reseau';
            setTimeout(() => { label.textContent = 'Synchroniser'; }, 3000);
        } finally {
            btn.disabled = false;
            icon.classList.remove('hidden');
            spinner.classList.add('hidden');
        }
    };

    // ═══ Period Switching (AJAX) ═══
    let currentPeriod = '{{ $period }}';
    const activeClass = 'bg-white shadow-sm text-indigo-700';
    const inactiveClass = 'text-gray-500 hover:text-gray-700';
    const baseClass = 'period-btn px-3 py-1.5 text-xs font-medium rounded-md transition';

    function updatePeriodButtons(period) {
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.className = baseClass + ' ' + (btn.dataset.period === period ? activeClass : inactiveClass);
        });
    }

    window.changePeriod = async function(period, force = false) {
        if (period === currentPeriod && !force) return;
        currentPeriod = period;
        updatePeriodButtons(period);

        try {
            const r = await fetch(`/ajax/dashboard/data?period=${period}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN },
            });
            if (!r.ok) { console.error('[Dashboard] HTTP', r.status); return; }
            const data = await r.json();
            if (data.error) { console.error('[Dashboard]', data.error); return; }

            const stats = data.stats || {};
            const summary = stats.summary || {};
            const counts = data.counts || {};
            const newAgentStats = data.agentStats || [];

            // Update KPIs
            const convCount = summary.conversations_count || 0;
            const resolutions = summary.resolutions_count || 0;
            const inMsg = summary.incoming_messages_count || 0;
            const outMsg = summary.outgoing_messages_count || 0;
            const rate = convCount > 0 ? Math.round((resolutions / convCount) * 100) : 0;

            document.getElementById('kpi-conversations').textContent = convCount;
            document.getElementById('kpi-resolutions').textContent = resolutions;
            document.getElementById('kpi-first-response').textContent = fmtTime(summary.avg_first_response_time || 0);
            document.getElementById('kpi-resolution-time').textContent = fmtTime(summary.avg_resolution_time || 0);
            document.getElementById('kpi-resolution-rate').textContent = rate + '%';
            document.getElementById('kpi-messages').textContent = inMsg + outMsg;
            document.getElementById('kpi-messages-detail').textContent = inMsg + ' entrants / ' + outMsg + ' sortants';

            // Update quick action numbers
            const qUnassigned = document.getElementById('quick-unassigned');
            const qOpen = document.getElementById('quick-open');
            const qPending = document.getElementById('quick-pending');
            if (qUnassigned) qUnassigned.textContent = counts.unassigned_count || 0;
            if (qOpen) qOpen.textContent = counts.all_count || 0;
            if (qPending) qPending.textContent = counts.pending_count || 0;

            // Update Conversation Trend Chart
            const newConvData = formatTimestamps(stats.conversations_count || []);
            const newResData = formatTimestamps(stats.resolutions || []);
            convTrendChart.data.labels = newConvData.labels;
            convTrendChart.data.datasets[0].data = newConvData.values;
            convTrendChart.data.datasets[1].data = newResData.values;
            convTrendChart.update('none');

            // Update Distribution Doughnut
            const newDist = [
                counts.mine_count || 0,
                counts.unassigned_count || 0,
                Math.max(0, (counts.assigned_count || 0) - (counts.mine_count || 0)),
                counts.pending_count || 0,
            ];
            convDistChart.data.datasets[0].data = newDist;
            convDistChart.update('none');

            // Update Message Chart
            const newInData = formatTimestamps(stats.incoming_messages || []);
            const newOutData = formatTimestamps(stats.outgoing_messages || []);
            msgTrendChart.data.labels = newInData.labels.length ? newInData.labels : newOutData.labels;
            msgTrendChart.data.datasets[0].data = newInData.values;
            msgTrendChart.data.datasets[1].data = newOutData.values;
            msgTrendChart.update('none');

            // Update Agent Chart
            const newAgentData = buildAgentChartData(newAgentStats);
            agentChart.data.labels = newAgentData.names;
            agentChart.data.datasets[0].data = newAgentData.convs;
            agentChart.data.datasets[1].data = newAgentData.resolved;
            agentChart.update('none');

            // Update source badge
            const syncLabel = document.getElementById('last-synced-label');
            if (syncLabel && data.lastSynced) {
                const d = new Date(data.lastSynced);
                const diff = Math.round((Date.now() - d.getTime()) / 60000);
                const diffText = diff < 1 ? 'a l\'instant' : diff < 60 ? 'il y a ' + diff + 'min' : 'il y a ' + Math.round(diff/60) + 'h';
                syncLabel.textContent = 'Synchro : ' + diffText;
            }

        } catch(e) {
            console.error('[Dashboard] Period change error:', e);
        }
    };

})();
</script>
@endpush
@endsection
