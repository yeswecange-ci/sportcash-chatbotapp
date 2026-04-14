@extends('layouts.app')
@section('title', 'Kash Bot — Dashboard')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Kash Bot — Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Vue d'ensemble des réclamations et escalades WhatsApp</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <label class="text-xs text-gray-500">Période :</label>
            <select name="period" onchange="this.form.submit()"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-indigo-500 bg-white">
                <option value="7"  {{ $period == 7  ? 'selected' : '' }}>7 jours</option>
                <option value="14" {{ $period == 14 ? 'selected' : '' }}>14 jours</option>
                <option value="30" {{ $period == 30 ? 'selected' : '' }}>30 jours</option>
            </select>
        </form>
    </div>

    {{-- KPIs RÉCLAMATIONS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total réclamations</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalReclamations }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">{{ $period }} derniers jours</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">En attente</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $enAttente }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">à traiter</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Résolues</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $resolues }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">{{ $period }} derniers jours</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 border-l-4 border-l-red-400">
            <p class="text-xs text-gray-500">Priorité haute</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $haute }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">{{ $period }} derniers jours</p>
        </div>
    </div>

    {{-- KPIs ESCALADES --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Escalades</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalEscalades }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">{{ $period }} derniers jours</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Escalades en attente</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $escaladeEnAttente }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">agent humain requis</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Escalades résolues</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $escaladeResolues }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">{{ $period }} derniers jours</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Taux d'escalade</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $tauxEscalade }}%</p>
            <p class="text-[10px] text-gray-400 mt-0.5">sur total interactions</p>
        </div>
    </div>

    {{-- GRAPHIQUE + PAR CANAL --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Volume par jour --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Réclamations / jour</h2>
            @if($parJour->count())
            <div class="flex items-end gap-1.5 h-32">
                @php $max = $parJour->max('total') ?: 1; @endphp
                @foreach($parJour as $j)
                <div class="flex-1 flex flex-col items-center gap-1 group">
                    <div class="w-full bg-indigo-500 rounded-t opacity-80 group-hover:opacity-100 transition"
                         style="height: {{ round(($j['total'] / $max) * 100) }}%"
                         title="{{ $j['jour'] }} : {{ $j['total'] }}">
                    </div>
                    <span class="text-[9px] text-gray-400 rotate-45 origin-left">{{ \Carbon\Carbon::parse($j['jour'])->format('d/m') }}</span>
                </div>
                @endforeach
            </div>
            @else
            <div class="h-32 flex items-center justify-center text-sm text-gray-400">Aucune donnée sur la période</div>
            @endif
        </div>

        {{-- Par canal --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Par canal</h2>
            @if(count($parCanal))
            <div class="space-y-3">
                @php
                $canalLabels = ['application' => 'Application', 'site_web' => 'Site web', 'ussd' => 'USSD', 'point_de_vente' => 'Point de vente'];
                $totalCanal  = array_sum($parCanal);
                @endphp
                @foreach($parCanal as $canal => $count)
                <div>
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span>{{ $canalLabels[$canal] ?? $canal }}</span>
                        <span class="font-semibold">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ round($count / $totalCanal * 100) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="h-24 flex items-center justify-center text-sm text-gray-400">Aucune donnée</div>
            @endif
        </div>
    </div>

    {{-- DERNIÈRES ACTIVITÉS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Dernières réclamations --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Dernières réclamations</h2>
                <a href="{{ route('kash.reclamations.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700">Voir tout →</a>
            </div>
            @forelse($dernieresReclamations as $r)
            <a href="{{ route('kash.reclamations.show', $r->reference) }}"
               class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition border-b border-gray-50 last:border-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono text-gray-400">{{ $r->reference }}</span>
                        @if($r->priorite === 'haute')
                        <span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-[10px] font-medium rounded">Haute</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-700 truncate mt-0.5">{{ $r->identifiant }}</p>
                    <p class="text-[11px] text-gray-400">{{ $r->canalLabel() }} · {{ $r->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full {{ $r->statutBadgeClass() }}">{{ $r->statutLabel() }}</span>
            </a>
            @empty
            <div class="px-5 py-8 text-center text-sm text-gray-400">Aucune réclamation</div>
            @endforelse
        </div>

        {{-- Dernières escalades --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">Dernières escalades</h2>
                <a href="{{ route('kash.escalades.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700">Voir tout →</a>
            </div>
            @forelse($dernieresEscalades as $e)
            <a href="{{ route('kash.escalades.show', $e->reference) }}"
               class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition border-b border-gray-50 last:border-0">
                <div class="flex-1 min-w-0">
                    <span class="text-xs font-mono text-gray-400">{{ $e->reference }}</span>
                    <p class="text-sm text-gray-700 truncate mt-0.5">{{ $e->identifiant }}</p>
                    <p class="text-[11px] text-gray-400">{{ $e->raison ?? 'Raison non précisée' }} · {{ $e->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full {{ $e->statutBadgeClass() }}">{{ $e->statutLabel() }}</span>
            </a>
            @empty
            <div class="px-5 py-8 text-center text-sm text-gray-400">Aucune escalade</div>
            @endforelse
        </div>
    </div>

</div>
</div>
@endsection
