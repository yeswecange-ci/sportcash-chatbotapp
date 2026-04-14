@extends('layouts.app')
@section('title', 'Dashboard Campagnes')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Dashboard Campagnes</h1>
                <p class="text-sm text-gray-500 mt-0.5">Vue d'ensemble de vos campagnes WhatsApp</p>
            </div>
            <div class="flex gap-1 bg-gray-100 rounded-lg p-0.5" id="period-selector">
                @foreach(['today' => "Aujourd'hui", 'week' => 'Semaine', 'month' => 'Mois'] as $p => $label)
                    <button onclick="changePeriod('{{ $p }}')"
                       data-period="{{ $p }}"
                       class="period-btn px-3 py-1.5 text-xs font-medium rounded-md transition
                              {{ $period === $p ? 'bg-white shadow-sm text-primary-700' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4" id="kpi-cards">
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Campagnes</p>
                </div>
                <p class="text-2xl font-bold text-gray-900" id="kpi-campaigns">{{ $kpis['total_campaigns'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5" id="kpi-campaigns-detail">{{ $kpis['active_campaigns'] }} active(s)</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Contacts</p>
                </div>
                <p class="text-2xl font-bold text-blue-600" id="kpi-contacts">{{ $kpis['total_contacts'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">au total</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Messages</p>
                </div>
                <p class="text-2xl font-bold text-violet-600" id="kpi-messages">{{ $kpis['total_messages'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">sur la période</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Delivres</p>
                </div>
                <p class="text-2xl font-bold text-emerald-600" id="kpi-delivered">{{ $kpis['delivered'] }}</p>
                <div class="flex items-center gap-1 mt-0.5">
                    <div class="flex-1 h-1 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-400 rounded-full transition-all" id="kpi-deliver-bar" style="width: {{ $kpis['deliver_rate'] }}%"></div>
                    </div>
                    <span class="text-[11px] text-emerald-600 font-semibold" id="kpi-deliver-rate">{{ $kpis['deliver_rate'] }}%</span>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Echoues</p>
                </div>
                <p class="text-2xl font-bold text-red-500" id="kpi-failed">{{ $kpis['failed'] }}</p>
                <div class="flex items-center gap-1 mt-0.5">
                    <div class="flex-1 h-1 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-red-400 rounded-full transition-all" id="kpi-fail-bar" style="width: {{ $kpis['fail_rate'] }}%"></div>
                    </div>
                    <span class="text-[11px] text-red-500 font-semibold" id="kpi-fail-rate">{{ $kpis['fail_rate'] }}%</span>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Tendance envois --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Évolution des envois</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">Nombre de messages par jour</p>
                    </div>
                    {{-- Légende manuelle --}}
                    <div class="flex items-center gap-4 text-[11px] text-gray-500">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-indigo-200 inline-block"></span>Total</span>
                        <span class="flex items-center gap-1.5"><span class="w-6 h-0.5 bg-emerald-500 inline-block rounded"></span>Livrés</span>
                        <span class="flex items-center gap-1.5"><span class="w-6 h-0.5 bg-red-400 inline-block rounded"></span>Échoués</span>
                    </div>
                </div>
                <div class="relative h-[240px]">
                    <canvas id="trend-chart"></canvas>
                    <div id="trend-empty" class="hidden absolute inset-0 flex flex-col items-center justify-center">
                        <svg class="w-10 h-10 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <p class="text-sm text-gray-400 font-medium">Aucun envoi sur cette période</p>
                        <p class="text-xs text-gray-300 mt-0.5">Les données s'afficheront après le premier envoi</p>
                    </div>
                </div>
            </div>

            {{-- Repartition statuts --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="mb-5">
                    <h3 class="text-sm font-semibold text-gray-900">Répartition des statuts</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Distribution par état de livraison</p>
                </div>
                <div class="relative h-[160px]">
                    <canvas id="status-chart"></canvas>
                    <div id="status-empty" class="hidden absolute inset-0 flex flex-col items-center justify-center">
                        <svg class="w-10 h-10 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                        <p class="text-xs text-gray-400">Aucun envoi sur la période</p>
                    </div>
                </div>
                {{-- Légende personnalisée --}}
                <div id="status-legend" class="mt-4 space-y-2"></div>
            </div>
        </div>

        {{-- Tableau recapitulatif campagnes --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Toutes les campagnes</h3>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="search-campaigns-table" placeholder="Filtrer..."
                           class="w-44 pl-9 pr-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="campaigns-table">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-gray-500 text-[11px] uppercase tracking-wider">Campagne</th>
                            <th class="text-center px-3 py-3 font-semibold text-gray-500 text-[11px] uppercase tracking-wider">Statut</th>
                            <th class="text-center px-3 py-3 font-semibold text-gray-500 text-[11px] uppercase tracking-wider">Contacts</th>
                            <th class="text-center px-3 py-3 font-semibold text-gray-500 text-[11px] uppercase tracking-wider">Envoyes</th>
                            <th class="text-center px-3 py-3 font-semibold text-gray-500 text-[11px] uppercase tracking-wider">Delivres</th>
                            <th class="text-center px-3 py-3 font-semibold text-gray-500 text-[11px] uppercase tracking-wider">Echoues</th>
                            <th class="text-center px-3 py-3 font-semibold text-gray-500 text-[11px] uppercase tracking-wider">Taux livraison</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="campaigns-tbody">
                        @foreach($campaigns as $c)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-5 py-3">
                                <a href="{{ route('campagnes.show', $c['id']) }}" class="font-medium text-gray-900 hover:text-primary-600 transition text-sm">{{ $c['name'] }}</a>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $c['status_class'] }}">{{ $c['status_label'] }}</span>
                            </td>
                            <td class="px-3 py-3 text-center text-sm text-gray-600">{{ $c['contacts'] }}</td>
                            <td class="px-3 py-3 text-center text-sm text-gray-600">{{ $c['sent'] }}</td>
                            <td class="px-3 py-3 text-center text-sm text-emerald-600 font-medium">{{ $c['delivered'] }}</td>
                            <td class="px-3 py-3 text-center text-sm text-red-500 font-medium">{{ $c['failed'] }}</td>
                            <td class="px-3 py-3 text-center">
                                @if($c['total'] > 0)
                                <div class="flex items-center gap-2 justify-center">
                                    <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $c['deliver_rate'] >= 80 ? 'bg-emerald-400' : ($c['deliver_rate'] >= 50 ? 'bg-amber-400' : 'bg-red-400') }}"
                                             style="width: {{ $c['deliver_rate'] }}%"></div>
                                    </div>
                                    <span class="text-[11px] font-bold {{ $c['deliver_rate'] >= 80 ? 'text-emerald-600' : ($c['deliver_rate'] >= 50 ? 'text-amber-600' : 'text-red-500') }}">
                                        {{ $c['deliver_rate'] }}%
                                    </span>
                                </div>
                                @else
                                <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($campaigns->isEmpty())
            <div class="px-5 py-12 text-center">
                <p class="text-sm text-gray-400">Aucune campagne</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function() {
    const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    let currentPeriod = '{{ $period }}';

    // ─── Defaults Chart.js ───────────────────────────────
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#6b7280';

    const STATUS_CONFIG = [
        { key: 'sent',         label: 'Envoyés',   color: '#60a5fa', bg: 'bg-blue-100',    text: 'text-blue-700'    },
        { key: 'delivered',    label: 'Livrés',    color: '#34d399', bg: 'bg-emerald-100', text: 'text-emerald-700' },
        { key: 'read',         label: 'Lus',       color: '#818cf8', bg: 'bg-indigo-100',  text: 'text-indigo-700'  },
        { key: 'failed',       label: 'Échoués',   color: '#f87171', bg: 'bg-red-100',     text: 'text-red-600'     },
        { key: 'queued',       label: 'En attente',color: '#d1d5db', bg: 'bg-gray-100',    text: 'text-gray-500'    },
    ];

    function formatDate(dateStr) {
        const d = new Date(dateStr + 'T00:00:00');
        return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
    }

    // ─── Plugin : texte centré dans doughnut ─────────────
    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw(chart) {
            if (chart.config.type !== 'doughnut') return;
            const total = chart.data.datasets[0].data.reduce((a, b) => a + (b || 0), 0);
            if (!total) return;
            const { ctx, chartArea } = chart;
            const cx = (chartArea.left + chartArea.right) / 2;
            const cy = (chartArea.top + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = 'bold 22px Inter, sans-serif';
            ctx.fillStyle = '#111827';
            ctx.fillText(total, cx, cy - 9);
            ctx.font = '10px Inter, sans-serif';
            ctx.fillStyle = '#9ca3af';
            ctx.fillText('messages', cx, cy + 10);
            ctx.restore();
        }
    };
    Chart.register(centerTextPlugin);

    // ─── TREND CHART (bar + lignes) ──────────────────────
    const trendRaw  = @json($trend);
    const trendCtx  = document.getElementById('trend-chart')?.getContext('2d');
    const trendEmpty = document.getElementById('trend-empty');
    let trendChart  = null;

    function buildTrendChart(data) {
        const hasData = data.some(d => (d.total || 0) > 0);
        if (!hasData) {
            trendEmpty?.classList.remove('hidden');
            if (trendCtx) trendCtx.canvas.style.visibility = 'hidden';
            return;
        }
        trendEmpty?.classList.add('hidden');
        if (trendCtx) trendCtx.canvas.style.visibility = '';

        const labels = data.map(d => formatDate(d.date));

        if (trendChart) {
            trendChart.data.labels = labels;
            trendChart.data.datasets[0].data = data.map(d => d.total     || 0);
            trendChart.data.datasets[1].data = data.map(d => d.delivered || 0);
            trendChart.data.datasets[2].data = data.map(d => d.failed    || 0);
            trendChart.update('active');
            return;
        }

        trendChart = new Chart(trendCtx, {
            data: {
                labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Total',
                        data: data.map(d => d.total || 0),
                        backgroundColor: 'rgba(99,102,241,0.12)',
                        borderColor: 'rgba(99,102,241,0.4)',
                        borderWidth: 1,
                        borderRadius: 5,
                        borderSkipped: false,
                        order: 2,
                    },
                    {
                        type: 'line',
                        label: 'Livrés',
                        data: data.map(d => d.delivered || 0),
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 2.5,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        order: 0,
                    },
                    {
                        type: 'line',
                        label: 'Échoués',
                        data: data.map(d => d.failed || 0),
                        borderColor: '#ef4444',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4,
                        borderDash: [4, 3],
                        pointRadius: 3,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        order: 1,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#94a3b8',
                        bodyColor: '#f1f5f9',
                        padding: { x: 12, y: 10 },
                        cornerRadius: 10,
                        titleFont: { size: 11, weight: '500' },
                        bodyFont: { size: 12, weight: '600' },
                        boxPadding: 5,
                        callbacks: {
                            title: (items) => items[0]?.label || '',
                            label: (ctx) => `  ${ctx.dataset.label} : ${ctx.parsed.y}`,
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6', drawBorder: false },
                        border: { display: false, dash: [4, 4] },
                        ticks: { precision: 0, stepSize: 1, padding: 8 }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { padding: 8 }
                    }
                }
            }
        });
    }

    buildTrendChart(trendRaw);

    // ─── STATUS DOUGHNUT ─────────────────────────────────
    const statusRaw  = @json($statusDistribution);
    const statusCtx  = document.getElementById('status-chart')?.getContext('2d');
    const statusEmpty  = document.getElementById('status-empty');
    const statusLegend = document.getElementById('status-legend');
    let statusChart = null;

    function buildStatusLegend(dist) {
        if (!statusLegend) return;
        const total = STATUS_CONFIG.reduce((s, c) => s + (dist[c.key] || 0), 0);
        if (!total) { statusLegend.innerHTML = ''; return; }

        statusLegend.innerHTML = STATUS_CONFIG
            .filter(c => (dist[c.key] || 0) > 0)
            .map(c => {
                const count = dist[c.key] || 0;
                const pct   = Math.round(count / total * 100);
                return `<div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:${c.color}"></span>
                    <span class="text-[11px] text-gray-600 flex-1">${c.label}</span>
                    <span class="text-[11px] font-semibold text-gray-800">${count}</span>
                    <span class="text-[10px] text-gray-400 w-8 text-right">${pct}%</span>
                </div>`;
            }).join('');
    }

    function buildStatusChart(dist) {
        const values = STATUS_CONFIG.map(c => dist[c.key] || 0);
        const total  = values.reduce((a, b) => a + b, 0);

        if (!total) {
            statusEmpty?.classList.remove('hidden');
            if (statusCtx) statusCtx.canvas.style.visibility = 'hidden';
            if (statusLegend) statusLegend.innerHTML = '';
            return;
        }
        statusEmpty?.classList.add('hidden');
        if (statusCtx) statusCtx.canvas.style.visibility = '';

        buildStatusLegend(dist);

        if (statusChart) {
            statusChart.data.datasets[0].data = values;
            statusChart.update('active');
            return;
        }

        statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: STATUS_CONFIG.map(c => c.label),
                datasets: [{
                    data: values,
                    backgroundColor: STATUS_CONFIG.map(c => c.color),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 8,
                    hoverBorderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        bodyColor: '#f1f5f9',
                        padding: { x: 12, y: 8 },
                        cornerRadius: 10,
                        bodyFont: { size: 12, weight: '600' },
                        callbacks: {
                            label: (ctx) => {
                                const v = ctx.parsed;
                                const t = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = t > 0 ? Math.round(v / t * 100) : 0;
                                return `  ${ctx.label} : ${v} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    buildStatusChart(statusRaw);

    // ─── FILTRE TABLEAU ──────────────────────────────────
    document.getElementById('search-campaigns-table')?.addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('#campaigns-tbody tr').forEach(row => {
            row.style.display = (row.querySelector('td')?.textContent || '').trim().toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // ─── CHANGEMENT DE PERIODE ───────────────────────────
    window.changePeriod = async function(period) {
        if (period === currentPeriod) return;
        currentPeriod = period;

        document.querySelectorAll('.period-btn').forEach(b => {
            const active = b.dataset.period === period;
            b.classList.toggle('bg-white',        active);
            b.classList.toggle('shadow-sm',       active);
            b.classList.toggle('text-primary-700', active);
            b.classList.toggle('text-gray-500',   !active);
        });

        try {
            const r = await fetch(`{{ route('ajax.campagnes.dashboard.data') }}?period=${period}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN }
            });
            const data = await r.json();

            // KPIs
            document.getElementById('kpi-campaigns').textContent        = data.kpis.total_campaigns;
            document.getElementById('kpi-campaigns-detail').textContent = data.kpis.active_campaigns + ' active(s)';
            document.getElementById('kpi-contacts').textContent         = data.kpis.total_contacts;
            document.getElementById('kpi-messages').textContent         = data.kpis.total_messages;
            document.getElementById('kpi-delivered').textContent        = data.kpis.delivered;
            document.getElementById('kpi-failed').textContent           = data.kpis.failed;

            const dr = data.kpis.deliver_rate;
            const fr = data.kpis.fail_rate;
            document.getElementById('kpi-deliver-rate').textContent = dr + '%';
            document.getElementById('kpi-fail-rate').textContent    = fr + '%';
            document.getElementById('kpi-deliver-bar').style.width  = dr + '%';
            document.getElementById('kpi-fail-bar').style.width     = fr + '%';

            // Charts
            buildTrendChart(data.trend);
            buildStatusChart(data.statusDistribution);
            buildStatusLegend(data.statusDistribution);

            // Table
            const tbody = document.getElementById('campaigns-tbody');
            if (tbody && data.campaigns) {
                tbody.innerHTML = data.campaigns.map(c => {
                    const dr = c.deliver_rate;
                    const barColor  = dr >= 80 ? 'bg-emerald-400' : (dr >= 50 ? 'bg-amber-400' : 'bg-red-400');
                    const textColor = dr >= 80 ? 'text-emerald-600' : (dr >= 50 ? 'text-amber-600' : 'text-red-500');
                    const rateHtml  = c.total > 0
                        ? `<div class="flex items-center gap-2 justify-center">
                               <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                   <div class="h-full rounded-full ${barColor}" style="width:${dr}%"></div>
                               </div>
                               <span class="text-[11px] font-bold ${textColor}">${dr}%</span>
                           </div>`
                        : '<span class="text-gray-300 text-xs">—</span>';
                    return `<tr class="hover:bg-gray-50/50 transition">
                        <td class="px-5 py-3"><a href="/campagnes/${c.id}" class="font-medium text-gray-900 hover:text-primary-600 transition text-sm">${c.name}</a></td>
                        <td class="px-3 py-3 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium ${c.status_class}">${c.status_label}</span></td>
                        <td class="px-3 py-3 text-center text-sm text-gray-600">${c.contacts}</td>
                        <td class="px-3 py-3 text-center text-sm text-gray-600">${c.sent}</td>
                        <td class="px-3 py-3 text-center text-sm text-emerald-600 font-medium">${c.delivered}</td>
                        <td class="px-3 py-3 text-center text-sm text-red-500 font-medium">${c.failed}</td>
                        <td class="px-3 py-3 text-center">${rateHtml}</td>
                    </tr>`;
                }).join('');
            }
        } catch(e) { console.error('Erreur chargement dashboard', e); }
    };
})();
</script>
@endpush
