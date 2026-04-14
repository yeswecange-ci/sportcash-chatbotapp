@extends('layouts.app')
@section('title', $reclamation->reference)

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 space-y-5">

    {{-- HEADER --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('kash.reclamations.index') }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition text-gray-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl font-bold text-gray-900 font-mono">{{ $reclamation->reference }}</h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $reclamation->statutBadgeClass() }}">{{ $reclamation->statutLabel() }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $reclamation->prioriteBadgeClass() }}">{{ $reclamation->prioriteLabel() }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-0.5">Créée {{ $reclamation->created_at->format('d/m/Y à H:i') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Colonne principale --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Infos client --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Informations client</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Identifiant</dt>
                        <dd class="text-gray-800 font-medium">{{ $reclamation->identifiant }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Numéro WhatsApp</dt>
                        <dd class="text-gray-800">{{ $reclamation->sender }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Canal</dt>
                        <dd class="text-gray-800">{{ $reclamation->canalLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Type de réclamation</dt>
                        <dd class="text-gray-800">{{ $reclamation->type_reclamation ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Résumé des infos collectées --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Informations collectées par Kash</h2>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $reclamation->infos ?? 'Aucune information.' }}</p>
            </div>

            {{-- Notes agent --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Notes de suivi</h2>
                <form id="notes-form" action="{{ route('kash.reclamations.notes', $reclamation) }}" method="POST">
                    @csrf @method('PATCH')
                    <textarea name="notes" rows="4" placeholder="Ajouter des notes de traitement..."
                              class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 resize-none">{{ $reclamation->notes }}</textarea>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-500 text-white text-sm font-medium rounded-lg hover:bg-indigo-600 transition">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar actions --}}
        <div class="space-y-4">

            {{-- Statut --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Statut</h3>
                <form action="{{ route('kash.reclamations.statut', $reclamation) }}" method="POST">
                    @csrf @method('PATCH')
                    <select name="statut" onchange="this.form.submit()"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                        @foreach(['en_attente' => 'En attente', 'en_cours' => 'En cours', 'resolue' => 'Résolue', 'fermee' => 'Fermée'] as $val => $label)
                        <option value="{{ $val }}" {{ $reclamation->statut === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Assignation --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Agent assigné</h3>
                <form action="{{ route('kash.reclamations.assign', $reclamation) }}" method="POST">
                    @csrf @method('PATCH')
                    <select name="assigned_to" onchange="this.form.submit()"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="">Non assigné</option>
                        @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ $reclamation->assigned_to == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Méta --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Détails</h3>
                <dl class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Priorité</dt>
                        <dd><span class="px-2 py-0.5 rounded-full font-medium {{ $reclamation->prioriteBadgeClass() }}">{{ $reclamation->prioriteLabel() }}</span></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Créée le</dt>
                        <dd class="text-gray-700">{{ $reclamation->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Mise à jour</dt>
                        <dd class="text-gray-700">{{ $reclamation->updated_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

</div>
</div>
@endsection
