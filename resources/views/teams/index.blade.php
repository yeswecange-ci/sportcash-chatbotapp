@extends('layouts.app')
@section('title', 'Equipes')

@section('content')
<div class="flex-1 overflow-y-auto">
    <div class="max-w-5xl mx-auto px-6 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Equipes</h1>
                <p class="text-sm text-gray-500 mt-1">{{ count($teams) }} equipe{{ count($teams) > 1 ? 's' : '' }} configuree{{ count($teams) > 1 ? 's' : '' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    {{ collect($teams)->sum(fn($t) => collect($t['members'] ?? [])->where('availability_status', 'online')->count()) }} en ligne
                </span>
                <button onclick="openCreateTeamModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouvelle equipe
                </button>
            </div>
        </div>

        @if(count($teams) > 0)
            {{-- Stats rapides --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl border border-gray-200 px-4 py-3.5">
                    <p class="text-2xl font-bold text-gray-900">{{ count($teams) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Equipes</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 px-4 py-3.5">
                    <p class="text-2xl font-bold text-gray-900">{{ collect($teams)->sum(fn($t) => count($t['members'] ?? [])) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Membres total</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 px-4 py-3.5">
                    <p class="text-2xl font-bold text-green-600">{{ collect($teams)->sum(fn($t) => collect($t['members'] ?? [])->where('availability_status', 'online')->count()) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">En ligne</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 px-4 py-3.5">
                    <p class="text-2xl font-bold text-gray-400">{{ collect($teams)->sum(fn($t) => collect($t['members'] ?? [])->where('availability_status', '!=', 'online')->count()) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Hors ligne</p>
                </div>
            </div>

            {{-- Grille equipes --}}
            <div id="teams-list" class="space-y-5">
                @foreach($teams as $team)
                    @php
                        $members = $team['members'] ?? [];
                        $online = collect($members)->where('availability_status', 'online')->values();
                        $colors = ['bg-indigo-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500', 'bg-violet-500'];
                        $teamColor = $colors[$loop->index % count($colors)];
                        $memberIds = collect($members)->pluck('id')->toArray();
                    @endphp

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow" id="team-card-{{ $team['id'] }}">
                        {{-- Team header --}}
                        <div class="px-6 py-5 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl {{ $teamColor }} text-white flex items-center justify-center font-bold text-base shadow-sm">
                                    {{ mb_strtoupper(mb_substr($team['name'], 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ $team['name'] }}</h3>
                                    @if($team['description'] ?? null)
                                        <p class="text-sm text-gray-500 mt-0.5">{{ $team['description'] }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($online->count() > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 text-green-700 rounded-full text-xs font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        {{ $online->count() }} en ligne
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">
                                    {{ count($members) }} membre{{ count($members) > 1 ? 's' : '' }}
                                </span>
                                {{-- Edit button --}}
                                <button onclick="openEditTeamModal({{ $team['id'] }}, '{{ addslashes($team['name']) }}', '{{ addslashes($team['description'] ?? '') }}')"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                {{-- Delete button --}}
                                <button onclick="deleteTeam({{ $team['id'] }}, '{{ addslashes($team['name']) }}')"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Supprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Members --}}
                        <div class="border-t border-gray-100 px-6 py-4">
                            {{-- Add member button --}}
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Membres</p>
                                <div class="relative">
                                    <button onclick="toggleAddMember({{ $team['id'] }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Ajouter
                                    </button>
                                    {{-- Dropdown agents --}}
                                    <div id="add-member-dropdown-{{ $team['id'] }}" class="hidden absolute right-0 top-full mt-1 w-64 bg-white border border-gray-200 rounded-xl shadow-lg z-20 max-h-60 overflow-y-auto">
                                        @foreach($agents as $agent)
                                            @if(!in_array($agent['id'], $memberIds))
                                                <button onclick="addMember({{ $team['id'] }}, {{ $agent['id'] }}, '{{ addslashes($agent['name'] ?? 'Agent') }}')"
                                                        class="w-full text-left px-4 py-2.5 hover:bg-gray-50 flex items-center gap-3 text-sm transition"
                                                        id="add-agent-{{ $team['id'] }}-{{ $agent['id'] }}">
                                                    @if($agent['thumbnail'] ?? null)
                                                        <img src="{{ $agent['thumbnail'] }}" class="w-7 h-7 rounded-full object-cover" alt="">
                                                    @else
                                                        <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">
                                                            {{ mb_strtoupper(mb_substr($agent['name'] ?? '?', 0, 2)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <p class="font-medium text-gray-900">{{ $agent['name'] ?? 'Agent' }}</p>
                                                        <p class="text-[11px] text-gray-400">{{ $agent['email'] ?? '' }}</p>
                                                    </div>
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            @if(count($members) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" id="team-members-{{ $team['id'] }}">
                                    @foreach($members as $member)
                                        @php $isOnline = ($member['availability_status'] ?? '') === 'online'; @endphp
                                        <div class="flex items-center gap-3 p-3 rounded-xl {{ $isOnline ? 'bg-green-50/50 border border-green-100' : 'bg-gray-50 border border-gray-100' }} transition hover:shadow-sm"
                                             id="member-{{ $team['id'] }}-{{ $member['id'] }}">
                                            <div class="relative flex-shrink-0">
                                                @if($member['thumbnail'] ?? null)
                                                    <img src="{{ $member['thumbnail'] }}" class="w-9 h-9 rounded-full object-cover" alt="">
                                                @else
                                                    <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">
                                                        {{ mb_strtoupper(mb_substr($member['name'] ?? '?', 0, 2)) }}
                                                    </div>
                                                @endif
                                                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white {{ $isOnline ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $member['name'] ?? 'Agent' }}</p>
                                                <p class="text-[11px] {{ $isOnline ? 'text-green-600' : 'text-gray-400' }}">
                                                    {{ $isOnline ? 'En ligne' : 'Hors ligne' }}
                                                </p>
                                            </div>
                                            @if($member['role'] ?? null)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 font-medium flex-shrink-0">
                                                    {{ ucfirst($member['role']) }}
                                                </span>
                                            @endif
                                            {{-- Remove member --}}
                                            <button onclick="removeMember({{ $team['id'] }}, {{ $member['id'] }}, '{{ addslashes($member['name'] ?? 'Agent') }}')"
                                                    class="flex-shrink-0 p-1 text-gray-300 hover:text-red-500 transition" title="Retirer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4" id="team-members-{{ $team['id'] }}">
                                    <p class="text-sm text-gray-400">Aucun membre dans cette equipe</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty state --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-16 text-center" id="teams-empty">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Aucune equipe configuree</h3>
                <p class="text-sm text-gray-500 max-w-sm mx-auto mb-4">Les equipes permettent d'organiser vos agents par specialite.</p>
                <button onclick="openCreateTeamModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Creer une equipe
                </button>
            </div>
        @endif
    </div>
</div>

{{-- Modal Create/Edit Team --}}
<div id="team-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 id="team-modal-title" class="text-lg font-semibold text-gray-900">Nouvelle equipe</h3>
        </div>
        <div class="px-6 py-5 space-y-4">
            <input type="hidden" id="team-modal-id" value="">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'equipe</label>
                <input id="team-modal-name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Ex: Support technique">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description (optionnel)</label>
                <textarea id="team-modal-desc" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none" placeholder="Description de l'equipe..."></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="closeTeamModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Annuler</button>
            <button onclick="saveTeam()" id="team-modal-save" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">Enregistrer</button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="toast" class="hidden fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium text-white transition-all duration-300"></div>
@endsection

@push('scripts')
<script>
const csrf = '{{ csrf_token() }}';

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium text-white transition-all duration-300 ' +
        (type === 'success' ? 'bg-green-600' : 'bg-red-600');
    setTimeout(() => t.classList.add('hidden'), 3000);
}

// ── Create / Edit Team Modal ─────────────
function openCreateTeamModal() {
    document.getElementById('team-modal-id').value = '';
    document.getElementById('team-modal-name').value = '';
    document.getElementById('team-modal-desc').value = '';
    document.getElementById('team-modal-title').textContent = 'Nouvelle equipe';
    document.getElementById('team-modal').classList.remove('hidden');
    document.getElementById('team-modal-name').focus();
}

function openEditTeamModal(id, name, desc) {
    document.getElementById('team-modal-id').value = id;
    document.getElementById('team-modal-name').value = name;
    document.getElementById('team-modal-desc').value = desc;
    document.getElementById('team-modal-title').textContent = 'Modifier l\'equipe';
    document.getElementById('team-modal').classList.remove('hidden');
    document.getElementById('team-modal-name').focus();
}

function closeTeamModal() {
    document.getElementById('team-modal').classList.add('hidden');
}

async function saveTeam() {
    const id = document.getElementById('team-modal-id').value;
    const name = document.getElementById('team-modal-name').value.trim();
    const desc = document.getElementById('team-modal-desc').value.trim();

    if (!name) { showToast('Le nom est requis', 'error'); return; }

    const btn = document.getElementById('team-modal-save');
    btn.disabled = true; btn.textContent = '...';

    try {
        const url = id ? `/ajax/teams/${id}` : '/ajax/teams';
        const method = id ? 'PUT' : 'POST';
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ name, description: desc || null })
        });
        const data = await res.json();
        if (data.success) {
            showToast(id ? 'Equipe modifiee' : 'Equipe creee');
            closeTeamModal();
            location.reload();
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (e) {
        showToast('Erreur reseau', 'error');
    }
    btn.disabled = false; btn.textContent = 'Enregistrer';
}

// ── Delete Team ─────────────
async function deleteTeam(id, name) {
    if (!confirm(`Supprimer l'equipe "${name}" ? Cette action est irreversible.`)) return;

    try {
        const res = await fetch(`/ajax/teams/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf }
        });
        const data = await res.json();
        if (data.success) {
            showToast('Equipe supprimee');
            const card = document.getElementById('team-card-' + id);
            if (card) card.remove();
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (e) {
        showToast('Erreur reseau', 'error');
    }
}

// ── Add / Remove Members ─────────────
function toggleAddMember(teamId) {
    const dd = document.getElementById('add-member-dropdown-' + teamId);
    // Close all other dropdowns first
    document.querySelectorAll('[id^="add-member-dropdown-"]').forEach(d => {
        if (d !== dd) d.classList.add('hidden');
    });
    dd.classList.toggle('hidden');
}

// Close dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="add-member-dropdown-"]') && !e.target.closest('button[onclick^="toggleAddMember"]')) {
        document.querySelectorAll('[id^="add-member-dropdown-"]').forEach(d => d.classList.add('hidden'));
    }
});

async function addMember(teamId, agentId, agentName) {
    try {
        const res = await fetch(`/ajax/teams/${teamId}/members`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ user_ids: [agentId] })
        });
        const data = await res.json();
        if (data.success) {
            showToast(`${agentName} ajoute a l'equipe`);
            location.reload();
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (e) {
        showToast('Erreur reseau', 'error');
    }
}

async function removeMember(teamId, agentId, agentName) {
    if (!confirm(`Retirer ${agentName} de l'equipe ?`)) return;

    try {
        const res = await fetch(`/ajax/teams/${teamId}/members`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ user_ids: [agentId] })
        });
        const data = await res.json();
        if (data.success) {
            showToast(`${agentName} retire de l'equipe`);
            const el = document.getElementById('member-' + teamId + '-' + agentId);
            if (el) el.remove();
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (e) {
        showToast('Erreur reseau', 'error');
    }
}

// Close modal on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeTeamModal();
});

// Close modal on backdrop click
document.getElementById('team-modal').addEventListener('click', function(e) {
    if (e.target === this) closeTeamModal();
});
</script>
@endpush
