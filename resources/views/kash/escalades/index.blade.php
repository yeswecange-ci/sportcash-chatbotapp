@extends('layouts.app')
@section('title', 'Escalades Kash')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Escalades Kash Bot</h1>
            <p class="text-sm text-gray-500 mt-0.5">Transferts vers un agent humain déclenchés par Kash</p>
        </div>
        <a href="{{ route('kash.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-700">← Dashboard</a>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Référence, identifiant, raison..."
                   class="pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 w-64">
        </div>
        <select name="statut" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500">
            <option value="">Tous statuts</option>
            @foreach(['en_attente' => 'En attente', 'en_cours' => 'En cours', 'resolue' => 'Résolue', 'fermee' => 'Fermée'] as $val => $label)
            <option value="{{ $val }}" {{ request('statut') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-indigo-500 text-white text-sm font-medium rounded-lg hover:bg-indigo-600 transition">Filtrer</button>
        @if(request()->hasAny(['search','statut']))
        <a href="{{ route('kash.escalades.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition">Réinitialiser</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Référence</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Identifiant</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Raison</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Chatwoot</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Assigné à</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($escalades as $e)
                <tr class="hover:bg-gray-50 transition cursor-pointer" onclick="window.location='{{ route('kash.escalades.show', $e->reference) }}'">
                    <td class="px-4 py-3 font-mono text-xs text-indigo-600 font-semibold">{{ $e->reference }}</td>
                    <td class="px-4 py-3 text-gray-700 max-w-[150px] truncate">{{ $e->identifiant }}</td>
                    <td class="px-4 py-3 text-gray-600 max-w-[180px] truncate">{{ $e->raison ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($e->chatwoot_conversation_id)
                        <span class="inline-flex items-center gap-1 text-xs text-green-700 bg-green-50 px-2 py-0.5 rounded">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            #{{ $e->chatwoot_conversation_id }}
                        </span>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $e->statutBadgeClass() }}">{{ $e->statutLabel() }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $e->agent?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs" title="{{ $e->created_at->format('d/m/Y H:i') }}">{{ $e->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-400">Aucune escalade trouvée</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($escalades->hasPages())
    <div class="mt-4">{{ $escalades->links() }}</div>
    @endif

</div>
</div>
@endsection
