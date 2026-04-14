@extends('layouts.app')
@section('title', 'Agents')

@section('content')
<div class="flex-1 overflow-y-auto">
    <div class="max-w-5xl mx-auto px-6 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Agents</h1>
                <p class="text-sm text-gray-500 mt-1">{{ count($agents) }} agent{{ count($agents) > 1 ? 's' : '' }}</p>
            </div>
            <button onclick="openCreateAgentModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvel agent
            </button>
        </div>

        {{-- Stats --}}
        @php
            $admins = collect($agents)->where('role', 'administrator')->count();
            $onlineCount = collect($agents)->where('availability_status', 'online')->count();
            $busyCount = collect($agents)->where('availability_status', 'busy')->count();
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3.5">
                <p class="text-2xl font-bold text-gray-900">{{ count($agents) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Total agents</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3.5">
                <p class="text-2xl font-bold text-purple-600">{{ $admins }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Administrateurs</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3.5">
                <p class="text-2xl font-bold text-green-600">{{ $onlineCount }}</p>
                <p class="text-xs text-gray-500 mt-0.5">En ligne</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3.5">
                <p class="text-2xl font-bold text-yellow-500">{{ $busyCount }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Occupe</p>
            </div>
        </div>

        {{-- Agents table --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Agent</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Role</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                        <th class="text-right px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="agents-tbody">
                    @foreach($agents as $agent)
                        @php $avail = $agent['availability_status'] ?? 'offline'; @endphp
                        <tr class="hover:bg-gray-50/50 transition" id="agent-row-{{ $agent['id'] }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative flex-shrink-0">
                                        @if($agent['thumbnail'] ?? null)
                                            <img src="{{ $agent['thumbnail'] }}" class="w-9 h-9 rounded-full object-cover" alt="">
                                        @else
                                            <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-sm font-bold">
                                                {{ mb_strtoupper(mb_substr($agent['name'] ?? '?', 0, 2)) }}
                                            </div>
                                        @endif
                                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white {{ $avail === 'online' ? 'bg-green-500' : ($avail === 'busy' ? 'bg-yellow-500' : 'bg-gray-300') }}"></span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $agent['name'] ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $agent['email'] ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full
                                             {{ ($agent['role'] ?? '') === 'administrator' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($agent['role'] ?? 'agent') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="flex items-center gap-1.5 text-sm">
                                    <span class="w-2 h-2 rounded-full {{ $avail === 'online' ? 'bg-green-500' : ($avail === 'busy' ? 'bg-yellow-500' : 'bg-gray-300') }}"></span>
                                    {{ $avail === 'online' ? 'En ligne' : ($avail === 'busy' ? 'Occupe' : 'Hors ligne') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="openEditAgentModal({{ $agent['id'] }}, '{{ addslashes($agent['name'] ?? '') }}', '{{ $agent['role'] ?? 'agent' }}')"
                                            class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button onclick="deleteAgent({{ $agent['id'] }}, '{{ addslashes($agent['name'] ?? 'Agent') }}')"
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if(count($agents) === 0)
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-gray-400">Aucun agent configure</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Create Agent --}}
<div id="agent-create-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Nouvel agent</h3>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                <input id="agent-create-name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="Ex: Jean Dupont">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="agent-create-email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="agent@example.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select id="agent-create-role" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                    <option value="agent">Agent</option>
                    <option value="administrator">Administrateur</option>
                </select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="closeCreateModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Annuler</button>
            <button onclick="createAgent()" id="agent-create-btn" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">Creer</button>
        </div>
    </div>
</div>

{{-- Modal Edit Agent --}}
<div id="agent-edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Modifier l'agent</h3>
        </div>
        <div class="px-6 py-5 space-y-4">
            <input type="hidden" id="agent-edit-id" value="">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <input id="agent-edit-name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select id="agent-edit-role" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                    <option value="agent">Agent</option>
                    <option value="administrator">Administrateur</option>
                </select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Annuler</button>
            <button onclick="updateAgent()" id="agent-edit-btn" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">Enregistrer</button>
        </div>
    </div>
</div>

{{-- Modal Credentials (after creation) --}}
<div id="agent-credentials-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Agent cree avec succes</h3>
        </div>
        <div class="px-6 py-5">
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <p class="text-sm text-amber-800">Communiquez ces identifiants a l'agent. Le mot de passe ne sera plus affiche.</p>
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
                    <div class="flex items-center gap-2">
                        <input id="cred-email" type="text" readonly class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono">
                        <button onclick="copyField('cred-email')" class="px-3 py-2 text-xs font-medium bg-gray-100 hover:bg-gray-200 rounded-lg transition">Copier</button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Mot de passe temporaire</label>
                    <div class="flex items-center gap-2">
                        <input id="cred-password" type="text" readonly class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono">
                        <button onclick="copyField('cred-password')" class="px-3 py-2 text-xs font-medium bg-gray-100 hover:bg-gray-200 rounded-lg transition">Copier</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button onclick="closeCredentialsModal()" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">Fermer</button>
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

// ── Create Agent ─────────────
function openCreateAgentModal() {
    document.getElementById('agent-create-name').value = '';
    document.getElementById('agent-create-email').value = '';
    document.getElementById('agent-create-role').value = 'agent';
    document.getElementById('agent-create-modal').classList.remove('hidden');
    document.getElementById('agent-create-name').focus();
}

function closeCreateModal() {
    document.getElementById('agent-create-modal').classList.add('hidden');
}

async function createAgent() {
    const name = document.getElementById('agent-create-name').value.trim();
    const email = document.getElementById('agent-create-email').value.trim();
    const role = document.getElementById('agent-create-role').value;

    if (!name || !email) { showToast('Nom et email requis', 'error'); return; }

    const btn = document.getElementById('agent-create-btn');
    btn.disabled = true; btn.textContent = '...';

    try {
        const res = await fetch('/ajax/agents', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ name, email, role })
        });
        const data = await res.json();
        if (data.success) {
            closeCreateModal();
            // Show credentials modal
            document.getElementById('cred-email').value = email;
            document.getElementById('cred-password').value = data.password || '';
            document.getElementById('agent-credentials-modal').classList.remove('hidden');
        } else {
            showToast(data.error || 'Erreur lors de la creation', 'error');
        }
    } catch (e) {
        showToast('Erreur reseau', 'error');
    }
    btn.disabled = false; btn.textContent = 'Creer';
}

// ── Edit Agent ─────────────
function openEditAgentModal(id, name, role) {
    document.getElementById('agent-edit-id').value = id;
    document.getElementById('agent-edit-name').value = name;
    document.getElementById('agent-edit-role').value = role;
    document.getElementById('agent-edit-modal').classList.remove('hidden');
    document.getElementById('agent-edit-name').focus();
}

function closeEditModal() {
    document.getElementById('agent-edit-modal').classList.add('hidden');
}

async function updateAgent() {
    const id = document.getElementById('agent-edit-id').value;
    const name = document.getElementById('agent-edit-name').value.trim();
    const role = document.getElementById('agent-edit-role').value;

    if (!name) { showToast('Le nom est requis', 'error'); return; }

    const btn = document.getElementById('agent-edit-btn');
    btn.disabled = true; btn.textContent = '...';

    try {
        const res = await fetch(`/ajax/agents/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ name, role })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Agent modifie');
            closeEditModal();
            location.reload();
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (e) {
        showToast('Erreur reseau', 'error');
    }
    btn.disabled = false; btn.textContent = 'Enregistrer';
}

// ── Delete Agent ─────────────
async function deleteAgent(id, name) {
    if (!confirm(`Supprimer l'agent "${name}" ? Cette action est irreversible.`)) return;

    try {
        const res = await fetch(`/ajax/agents/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf }
        });
        const data = await res.json();
        if (data.success) {
            showToast('Agent supprime');
            const row = document.getElementById('agent-row-' + id);
            if (row) row.remove();
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (e) {
        showToast('Erreur reseau', 'error');
    }
}

// ── Credentials Modal ─────────────
function closeCredentialsModal() {
    document.getElementById('agent-credentials-modal').classList.add('hidden');
    location.reload();
}

function copyField(inputId) {
    const input = document.getElementById(inputId);
    navigator.clipboard.writeText(input.value).then(() => {
        showToast('Copie !');
    });
}

// Close modals on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeCreateModal();
        closeEditModal();
    }
});

// Close modals on backdrop click
document.getElementById('agent-create-modal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
});
document.getElementById('agent-edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
document.getElementById('agent-credentials-modal').addEventListener('click', function(e) {
    if (e.target === this) closeCredentialsModal();
});
</script>
@endpush
