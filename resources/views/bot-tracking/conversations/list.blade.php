@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Conversations</h1>
                <p class="text-sm text-gray-500 mt-0.5">Historique complet des conversations du bot</p>
            </div>
            <a href="{{ route('bot-tracking.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">&larr; Dashboard</a>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalStats['total'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Total</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ number_format($totalStats['active'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Actives</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ number_format($totalStats['completed'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Completees</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <form method="GET" action="{{ route('bot-tracking.conversations') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 flex-1 min-w-48">
                    <label class="text-xs font-medium text-gray-600">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, telephone..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Statut</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <option value="">Tous</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Complete</option>
                        <option value="timeout" {{ request('status') == 'timeout' ? 'selected' : '' }}>Timeout</option>
                        <option value="abandoned" {{ request('status') == 'abandoned' ? 'selected' : '' }}>Abandonne</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Type</label>
                    <select name="is_client" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <option value="">Tous</option>
                        <option value="1" {{ request('is_client') === '1' ? 'selected' : '' }}>Clients</option>
                        <option value="0" {{ request('is_client') === '0' ? 'selected' : '' }}>Non-clients</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Du</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Au</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">Filtrer</button>
                <a href="{{ route('bot-tracking.conversations') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Reinitialiser</a>
            </form>
        </div>

        {{-- Conversations Table --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client / Telephone</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Menu actuel</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duree</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($conversations as $conv)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $conv->display_name ?? 'Inconnu' }}</div>
                                <div class="text-xs text-gray-400">{{ $conv->phone_number }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $sm = ['active' => 'bg-green-100 text-green-700', 'completed' => 'bg-blue-100 text-blue-700', 'timeout' => 'bg-amber-100 text-amber-700', 'abandoned' => 'bg-gray-100 text-gray-600'];
                                    $sc = $sm[$conv->status] ?? 'bg-gray-100 text-gray-600';
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
                                    {{ \Carbon\Carbon::parse($conv->started_at)->format('d/m/Y H:i') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('bot-tracking.conversations.show', $conv->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Voir &rarr;</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">Aucune conversation trouvee.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($conversations->hasPages())
            <div class="mt-4 border-t border-gray-100 pt-4">
                {{ $conversations->appends(request()->query())->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
