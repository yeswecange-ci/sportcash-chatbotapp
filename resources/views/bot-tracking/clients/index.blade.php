@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Clients &amp; Contacts</h1>
                <p class="text-sm text-gray-500 mt-0.5">Base de contacts du bot WhatsApp</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('bot-tracking.clients.sync') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Synchroniser</a>
                <a href="{{ route('bot-tracking.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">&larr; Dashboard</a>
            </div>
        </div>
        {{-- Stats Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_clients'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Total contacts</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats['sportcash_clients'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Clients SportCash</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-orange-500">{{ number_format($stats['non_clients'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Non-clients</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['recent_clients'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Recents (30j)</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_interactions'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Interactions</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_conversations'] ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Conversations</p>
            </div>
        </div>
        {{-- Filters --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <form method="GET" action="{{ route('bot-tracking.clients.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 flex-1 min-w-48">
                    <label class="text-xs font-medium text-gray-600">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, telephone, email..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Type</label>
                    <select name="is_client" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Tous</option>
                        <option value="true">Clients SportCash</option>
                        <option value="false">Non-clients</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-600">Trier par</label>
                    <select name="sort_by" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="last_interaction_at" {{ request('sort_by') == 'last_interaction_at' ? 'selected' : '' }}>Derniere interaction</option>
                        <option value="client_full_name" {{ request('sort_by') == 'client_full_name' ? 'selected' : '' }}>Nom</option>
                        <option value="interaction_count" {{ request('sort_by') == 'interaction_count' ? 'selected' : '' }}>Nb interactions</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date creation</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition shadow-sm">Filtrer</button>
                <a href="{{ route('bot-tracking.clients.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Reinitialiser</a>
            </form>
        </div>
        {{-- Clients Table --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telephone / Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Interactions</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Derniere activite</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($clients as $client)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-indigo-700">{{ strtoupper(substr($client->client_full_name ?? $client->whatsapp_profile_name ?? '?', 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $client->client_full_name ?? $client->whatsapp_profile_name ?? 'Inconnu' }}</p>
                                        @if($client->carte_vip)<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">VIP</span>@endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-700">{{ $client->phone_number }}</p>
                                @if($client->email)<p class="text-xs text-gray-400">{{ $client->email }}</p>@endif
                            </td>
                            <td class="px-4 py-3">
                                @if($client->is_client === true || $client->is_client == 1)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">Client SportCash</span>
                                @elseif($client->is_client === false || $client->is_client == 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Non-client</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Non defini</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $client->interaction_count ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $client->last_interaction_at ? \Carbon\Carbon::parse($client->last_interaction_at)->diffForHumans() : '&mdash;' }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('bot-tracking.clients.show', $client->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Voir &rarr;</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">Aucun contact trouve.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($clients->hasPages())<div class="mt-4 border-t border-gray-100 pt-4">{{ $clients->appends(request()->query())->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
