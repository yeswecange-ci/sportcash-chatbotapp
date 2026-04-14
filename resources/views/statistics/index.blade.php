@extends('layouts.app')
@section('title', 'Statistiques')

@section('content')
<div class="flex flex-col h-full overflow-y-auto bg-gray-50">

    {{-- Header --}}
    <div class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Statistiques</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Vue d'ensemble des performances
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
                @foreach(['today' => 'Aujourd\'hui', 'week' => 'Semaine', 'month' => 'Mois', 'quarter' => 'Trimestre'] as $val => $label)
                    <button onclick="changePeriod('{{ $val }}')"
                       data-period="{{ $val }}"
                       class="period-btn px-3 py-1.5 text-xs font-medium rounded-md transition {{ $currentPeriod === $val ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            </div>
        </div>
    </div>

    @php
        $summary = $stats['summary'] ?? [];
        $counts = $stats['counts'] ?? [];

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
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Conversations</p>
                </div>
                <p class="text-2xl font-bold text-gray-900" id="kpi-conversations">{{ $convCount }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Resolues</p>
                </div>
                <p class="text-2xl font-bold text-green-600" id="kpi-resolutions">{{ $resolutions }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">1ere reponse</p>
                </div>
                <p class="text-2xl font-bold text-blue-600" id="kpi-first-response">{{ $fmtTime($avgFirstResp) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Resolution moy.</p>
                </div>
                <p class="text-2xl font-bold text-purple-600" id="kpi-resolution-time">{{ $fmtTime($avgResolution) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Taux resolution</p>
                </div>
                <p class="text-2xl font-bold text-amber-600" id="kpi-resolution-rate">{{ $resolutionRate }}%</p>
            </div>
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

        {{-- Row 1: Conversation + Resolution Trends --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Conversations + Resolutions Area Chart --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Conversations et resolutions</h3>
                    <div class="flex items-center gap-4 text-[10px]">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Conversations</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Resolues</span>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="convTrendChart"></canvas>
                </div>
            </div>

            {{-- Messages Volume --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Volume de messages</h3>
                    <div class="flex items-center gap-4 text-[10px]">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> Entrants</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span> Sortants</span>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="msgTrendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Row 2: Distribution + Message Ratio --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Distribution Doughnut --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Repartition conversations</h3>
                <div class="h-48 flex items-center justify-center">
                    <canvas id="convDistChart"></canvas>
                </div>
                <div class="mt-4 space-y-2" id="dist-legend">
                    @php
                        $distItems = [
                            ['label' => 'Mes conversations', 'count' => $counts['mine_count'] ?? 0, 'color' => '#6366f1'],
                            ['label' => 'Non assignees', 'count' => $counts['unassigned_count'] ?? 0, 'color' => '#f97316'],
                            ['label' => 'Assignees', 'count' => $counts['assigned_count'] ?? 0, 'color' => '#3b82f6'],
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

            {{-- Messages Ratio Pie --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Ratio messages</h3>
                <div class="h-48 flex items-center justify-center">
                    <canvas id="msgRatioChart"></canvas>
                </div>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500 flex-shrink-0"></span>
                            <span class="text-gray-600">Entrants</span>
                        </span>
                        <span class="font-semibold text-gray-900" id="ratio-incoming">{{ $incomingMsgs }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-violet-500 flex-shrink-0"></span>
                            <span class="text-gray-600">Sortants</span>
                        </span>
                        <span class="font-semibold text-gray-900" id="ratio-outgoing">{{ $outgoingMsgs }}</span>
                    </div>
                </div>
            </div>

            {{-- Agent Activity Radar --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Activite agents (top 5)</h3>
                <div class="h-48 flex items-center justify-center">
                    <canvas id="agentRadarChart"></canvas>
                </div>
                <p class="text-[10px] text-gray-400 text-center mt-3">Conversations et resolutions par agent</p>
            </div>
        </div>

        {{-- Agent Leaderboard --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Classement des agents</h3>
                <span class="text-[10px] text-gray-400 font-medium" id="agent-count">{{ count($agents) }} agents</span>
            </div>
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
                            $leaderboard = is_array($agentLeaderboard) ? $agentLeaderboard : [];
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
                                <td class="py-2.5 px-3">
                                    @if($i === 0)
                                        <span class="text-amber-500 text-sm">&#9733;</span>
                                    @elseif($i === 1)
                                        <span class="text-gray-400 text-sm">&#9733;</span>
                                    @elseif($i === 2)
                                        <span class="text-amber-700 text-sm">&#9733;</span>
                                    @else
                                        <span class="text-xs text-gray-400 font-mono">{{ $i + 1 }}</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full {{ $avatarColor }} flex items-center justify-center font-semibold text-[10px]">
                                            {{ mb_substr($agentName, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $agentName }}</span>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <span class="text-sm font-semibold text-gray-900">{{ $convs }}</span>
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <span class="inline-flex items-center gap-1 text-sm text-green-600 font-medium">
                                        {{ $resolved }}
                                        @if($convs > 0)
                                            <span class="text-[10px] text-gray-400">({{ round(($resolved / $convs) * 100) }}%)</span>
                                        @endif
                                    </span>
                                </td>
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

        {{-- Bottom stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center hover:shadow-md transition-shadow">
                <p class="text-3xl font-bold text-gray-900">{{ count($agents) }}</p>
                <p class="text-[10px] text-gray-400 uppercase font-semibold mt-1">Agents actifs</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center hover:shadow-md transition-shadow">
                <p class="text-3xl font-bold text-indigo-600" id="stat-open">{{ $counts['all_count'] ?? 0 }}</p>
                <p class="text-[10px] text-gray-400 uppercase font-semibold mt-1">Conv. ouvertes</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center hover:shadow-md transition-shadow">
                <p class="text-3xl font-bold text-orange-500" id="stat-unassigned">{{ $counts['unassigned_count'] ?? 0 }}</p>
                <p class="text-[10px] text-gray-400 uppercase font-semibold mt-1">Non assignees</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center hover:shadow-md transition-shadow">
                <p class="text-3xl font-bold text-green-600" id="stat-resolved">{{ $counts['resolved_count'] ?? 0 }}</p>
                <p class="text-[10px] text-gray-400 uppercase font-semibold mt-1">Resolues (total)</p>
            </div>
        </div>

    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

@push('scripts')
<script>
(function() {
    const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#9ca3af';
    Chart.defaults.plugins.legend.display = false;

    // ═══ Plugin : message "Aucune donnée" ═══
    const noDataPlugin = {
        id: 'noData',
        afterDraw(chart) {
            const hasLabels = Array.isArray(chart.data.labels) && chart.data.labels.length > 0;
            const { ctx, chartArea: a } = chart;
            if (!a) return;

            if (!hasLabels) {
                // Aucun label du tout — données pas encore chargées
                ctx.save();
                const cx = a.left + (a.right - a.left) / 2;
                const cy = a.top  + (a.bottom - a.top) / 2;
                ctx.fillStyle = 'rgba(249,250,251,0.9)';
                ctx.fillRect(a.left, a.top, a.right - a.left, a.bottom - a.top);
                ctx.fillStyle = '#9ca3af';
                ctx.font = '12px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('Aucune donnée disponible', cx, cy + 4);
                ctx.font = '10px Inter, sans-serif';
                ctx.fillStyle = '#d1d5db';
                ctx.fillText('Synchronisez les données pour afficher ce graphique', cx, cy + 22);
                ctx.restore();
                return;
            }

            // Pour les graphiques barre : indiquer si toutes les valeurs sont à 0
            if (chart.config.type === 'bar') {
                const allZero = chart.data.datasets.every(ds =>
                    (ds.data || []).every(v => !v || v === 0)
                );
                if (allZero) {
                    ctx.save();
                    const cx = a.left + (a.right - a.left) / 2;
                    const cy = a.top  + (a.bottom - a.top) / 2;
                    ctx.fillStyle = 'rgba(249,250,251,0.8)';
                    ctx.fillRect(a.left + 1, a.top + 1, a.right - a.left - 2, a.bottom - a.top - 2);
                    ctx.fillStyle = '#9ca3af';
                    ctx.font = '12px Inter, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText('Aucun message enregistré pour cette période', cx, cy + 4);
                    ctx.font = '10px Inter, sans-serif';
                    ctx.fillStyle = '#d1d5db';
                    ctx.fillText('Cliquez sur "Synchroniser" pour actualiser les données', cx, cy + 22);
                    ctx.restore();
                }
            }
        },
    };
    Chart.register(noDataPlugin);

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

    const tooltipStyle = { backgroundColor: '#1f2937', padding: 10, cornerRadius: 8, titleFont: { size: 12 }, bodyFont: { size: 11 } };

    // ═══ Initial data ═══
    const initConvTrend = @json($stats['trends'] ?? []);
    const initResTrend = @json($stats['resolution_trends'] ?? []);
    const initIncoming = @json($stats['incoming_trends'] ?? []);
    const initOutgoing = @json($stats['outgoing_trends'] ?? []);
    const initAgentLeaderboard = @json($agentLeaderboard ?? []);
    const initAgents = @json($agents ?? []);
    const initCounts = @json($counts ?? []);
    const initSummary = @json($stats['summary'] ?? []);

    const agentMap = {};
    (initAgents || []).forEach(a => { agentMap[a.id] = a; });

    // ═══ 1. Conversation + Resolution Area Chart ═══
    const convCtx = document.getElementById('convTrendChart').getContext('2d');
    const convData = formatTimestamps(initConvTrend);
    const resData = formatTimestamps(initResTrend);

    const convTrendChart = new Chart(convCtx, {
        type: 'line',
        data: {
            labels: convData.labels,
            datasets: [
                {
                    label: 'Conversations',
                    data: convData.values,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
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
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
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
                x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { precision: 0 } },
            },
            plugins: { tooltip: tooltipStyle },
        },
    });

    // ═══ 2. Messages Volume Bar Chart ═══
    const msgCtx = document.getElementById('msgTrendChart').getContext('2d');
    const inData = formatTimestamps(initIncoming);
    const outData = formatTimestamps(initOutgoing);
    console.log('[Stats] source:', '{{ $stats["source"] ?? "?" }}');
    console.log('[Stats] incoming_trends:', initIncoming?.length, 'points', initIncoming?.slice(0,3));
    console.log('[Stats] outgoing_trends:', initOutgoing?.length, 'points', initOutgoing?.slice(0,3));
    console.log('[Stats] inData.labels:', inData.labels?.length, '| values sum:', inData.values?.reduce((a,b)=>a+b,0));

    const msgTrendChart = new Chart(msgCtx, {
        type: 'bar',
        data: {
            labels: inData.labels.length ? inData.labels : outData.labels,
            datasets: [
                { label: 'Entrants', data: inData.values, backgroundColor: 'rgba(14, 165, 233, 0.7)', borderRadius: 4, barPercentage: 0.6 },
                { label: 'Sortants', data: outData.values, backgroundColor: 'rgba(139, 92, 246, 0.7)', borderRadius: 4, barPercentage: 0.6 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { precision: 0 } },
            },
            plugins: { tooltip: tooltipStyle },
        },
    });

    // ═══ 3. Conversation Distribution Doughnut ═══
    const distCtx = document.getElementById('convDistChart').getContext('2d');
    const convDistChart = new Chart(distCtx, {
        type: 'doughnut',
        data: {
            labels: ['Mes conversations', 'Non assignees', 'Assignees', 'En attente'],
            datasets: [{
                data: [initCounts.mine_count || 0, initCounts.unassigned_count || 0, initCounts.assigned_count || 0, initCounts.pending_count || 0],
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
                    ...tooltipStyle,
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

    // ═══ 4. Message Ratio Doughnut ═══
    const ratioCtx = document.getElementById('msgRatioChart').getContext('2d');
    const msgRatioChart = new Chart(ratioCtx, {
        type: 'doughnut',
        data: {
            labels: ['Entrants', 'Sortants'],
            datasets: [{
                data: [initSummary.incoming_messages_count || 0, initSummary.outgoing_messages_count || 0],
                backgroundColor: ['#0ea5e9', '#8b5cf6'],
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
                    ...tooltipStyle,
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

    // ═══ 5. Agent Radar Chart (top 5) ═══
    const radarCtx = document.getElementById('agentRadarChart').getContext('2d');

    function buildRadarData(leaderboard) {
        const top5 = (Array.isArray(leaderboard) ? leaderboard : []).slice(0, 5);
        const labels = top5.map(e => {
            const info = agentMap[e.id];
            return info ? info.name.split(' ')[0] : 'Agent';
        });
        const convs = top5.map(e => e.metric?.conversations_count || 0);
        const resolved = top5.map(e => e.metric?.resolutions_count || 0);
        return { labels, convs, resolved };
    }

    const radarData = buildRadarData(initAgentLeaderboard);

    const agentRadarChart = new Chart(radarCtx, {
        type: 'radar',
        data: {
            labels: radarData.labels,
            datasets: [
                {
                    label: 'Conversations',
                    data: radarData.convs,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.15)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#6366f1',
                },
                {
                    label: 'Resolues',
                    data: radarData.resolved,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.15)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#22c55e',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.06)' },
                    angleLines: { color: 'rgba(0,0,0,0.06)' },
                    ticks: { display: false },
                    pointLabels: { font: { size: 10 } },
                },
            },
            plugins: { tooltip: tooltipStyle },
        },
    });

    // ═══ AJAX Period Switching ═══
    let currentPeriod = '{{ $currentPeriod }}';
    const activeClass = 'bg-white shadow-sm text-indigo-700';
    const inactiveClass = 'text-gray-500 hover:text-gray-700';
    const baseClass = 'period-btn px-3 py-1.5 text-xs font-medium rounded-md transition';

    function updatePeriodButtons(period) {
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.className = baseClass + ' ' + (btn.dataset.period === period ? activeClass : inactiveClass);
        });
    }

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
            const r = await fetch('{{ route("ajax.statistics.sync") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ period: 'all' }),
            });
            const res = await r.json();
            if (res.success) {
                const syncLabel = document.getElementById('last-synced-label');
                if (syncLabel) {
                    syncLabel.textContent = 'Synchro : a l\'instant';
                    syncLabel.classList.remove('text-amber-500');
                    syncLabel.classList.add('text-gray-400');
                }
                await changePeriod(currentPeriod, true);
            } else {
                setTimeout(() => { label.textContent = 'Synchroniser'; }, 3000);
            }
        } catch(e) {
            setTimeout(() => { label.textContent = 'Synchroniser'; }, 3000);
        } finally {
            btn.disabled = false;
            icon.classList.remove('hidden');
            spinner.classList.add('hidden');
            label.textContent = 'Synchroniser';
        }
    };

    window.changePeriod = async function(period, force = false) {
        if (period === currentPeriod && !force) return;
        currentPeriod = period;
        updatePeriodButtons(period);

        try {
            const r = await fetch(`/ajax/statistics/data?period=${period}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN },
            });
            if (!r.ok) { console.error('[Statistics] HTTP', r.status); return; }
            const data = await r.json();
            if (data.error) { console.error('[Statistics]', data.error); return; }

            const stats = data.stats || {};
            const summary = stats.summary || {};
            const counts = stats.counts || {};
            const leaderboard = data.leaderboard || [];

            // KPIs
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
            document.getElementById('ratio-incoming').textContent = inMsg;
            document.getElementById('ratio-outgoing').textContent = outMsg;

            // Bottom stats
            const sOpen = document.getElementById('stat-open');
            const sUnassigned = document.getElementById('stat-unassigned');
            const sResolved = document.getElementById('stat-resolved');
            if (sOpen) sOpen.textContent = counts.all_count || 0;
            if (sUnassigned) sUnassigned.textContent = counts.unassigned_count || 0;
            if (sResolved) sResolved.textContent = counts.resolved_count || 0;

            // Conv Trend Chart
            const newConvData = formatTimestamps(stats.trends || []);
            const newResData = formatTimestamps(stats.resolution_trends || []);
            convTrendChart.data.labels = newConvData.labels;
            convTrendChart.data.datasets[0].data = newConvData.values;
            convTrendChart.data.datasets[1].data = newResData.values;
            convTrendChart.update('none');

            // Message Chart
            const newInData = formatTimestamps(stats.incoming_trends || []);
            const newOutData = formatTimestamps(stats.outgoing_trends || []);
            msgTrendChart.data.labels = newInData.labels.length ? newInData.labels : newOutData.labels;
            msgTrendChart.data.datasets[0].data = newInData.values;
            msgTrendChart.data.datasets[1].data = newOutData.values;
            msgTrendChart.update('none');

            // Distribution
            convDistChart.data.datasets[0].data = [counts.mine_count || 0, counts.unassigned_count || 0, counts.assigned_count || 0, counts.pending_count || 0];
            convDistChart.update('none');

            // Message Ratio
            msgRatioChart.data.datasets[0].data = [inMsg, outMsg];
            msgRatioChart.update('none');

            // Radar
            const newRadar = buildRadarData(leaderboard);
            agentRadarChart.data.labels = newRadar.labels;
            agentRadarChart.data.datasets[0].data = newRadar.convs;
            agentRadarChart.data.datasets[1].data = newRadar.resolved;
            agentRadarChart.update('none');

            // Rebuild agent table
            const tbody = document.getElementById('agent-tbody');
            if (tbody && Array.isArray(leaderboard)) {
                const colors = ['bg-indigo-100 text-indigo-600', 'bg-green-100 text-green-600', 'bg-blue-100 text-blue-600', 'bg-amber-100 text-amber-600', 'bg-rose-100 text-rose-600'];
                const stars = ['<span class="text-amber-500 text-sm">&#9733;</span>', '<span class="text-gray-400 text-sm">&#9733;</span>', '<span class="text-amber-700 text-sm">&#9733;</span>'];

                if (leaderboard.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-sm text-gray-400">Aucune donnee disponible pour cette periode</td></tr>';
                } else {
                    tbody.innerHTML = leaderboard.map((e, i) => {
                        const info = agentMap[e.id];
                        const name = info ? info.name : ('Agent #' + e.id);
                        const convs = e.metric?.conversations_count || 0;
                        const resolved = e.metric?.resolutions_count || 0;
                        const pct = convs > 0 ? Math.round((resolved / convs) * 100) : 0;
                        const rank = i < 3 ? stars[i] : `<span class="text-xs text-gray-400 font-mono">${i + 1}</span>`;
                        const color = colors[i % colors.length];
                        return `<tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-2.5 px-3">${rank}</td>
                            <td class="py-2.5 px-3"><div class="flex items-center gap-2"><div class="w-7 h-7 rounded-full ${color} flex items-center justify-center font-semibold text-[10px]">${name.charAt(0)}</div><span class="text-sm font-medium text-gray-900">${name}</span></div></td>
                            <td class="py-2.5 px-3 text-right"><span class="text-sm font-semibold text-gray-900">${convs}</span></td>
                            <td class="py-2.5 px-3 text-right"><span class="inline-flex items-center gap-1 text-sm text-green-600 font-medium">${resolved}${convs > 0 ? ` <span class="text-[10px] text-gray-400">(${pct}%)</span>` : ''}</span></td>
                            <td class="py-2.5 px-3 text-right text-sm text-gray-600">${fmtTime(e.metric?.avg_first_response_time || 0)}</td>
                            <td class="py-2.5 px-3 text-right text-sm text-gray-600">${fmtTime(e.metric?.avg_resolution_time || 0)}</td>
                        </tr>`;
                    }).join('');
                }
            }

            // Update last-synced label
            const syncLabel = document.getElementById('last-synced-label');
            if (syncLabel && data.lastSynced) {
                const d = new Date(data.lastSynced);
                const diff = Math.round((Date.now() - d.getTime()) / 60000);
                const diffText = diff < 1 ? 'a l\'instant' : diff < 60 ? 'il y a ' + diff + 'min' : 'il y a ' + Math.round(diff/60) + 'h';
                syncLabel.textContent = 'Synchro : ' + diffText;
                syncLabel.classList.remove('text-amber-500');
                syncLabel.classList.add('text-gray-400');
            }

        } catch(e) {
            console.error('[Statistics] Period change error:', e);
        }
    };

})();
</script>
@endpush
@endsection
