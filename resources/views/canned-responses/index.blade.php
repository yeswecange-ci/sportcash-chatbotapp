@extends('layouts.app')
@section('title', 'Reponses rapides')

@section('content')
<div class="flex-1 overflow-y-auto">
    <div class="max-w-4xl mx-auto px-6 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Reponses rapides</h1>
                <p class="text-sm text-gray-500 mt-1">Tapez <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-200 rounded text-xs font-mono">/</kbd> dans le chat pour inserer une reponse rapide</p>
            </div>
            <button onclick="openModal()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvelle reponse
            </button>
        </div>

        {{-- Liste --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            @forelse($responses as $resp)
                <div class="flex items-start gap-4 px-5 py-4 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition group" data-id="{{ $resp['id'] }}">
                    <div class="flex-shrink-0 mt-0.5">
                        <span class="inline-flex items-center px-2.5 py-1 bg-primary-50 text-primary-700 rounded-lg text-xs font-mono font-semibold">
                            /{{ $resp['short_code'] }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $resp['content'] }}</p>
                    </div>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition flex-shrink-0">
                        <button onclick="openModal({{ json_encode($resp) }})"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button onclick="deleteResponse({{ $resp['id'] }})"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <p class="text-gray-500 text-sm">Aucune reponse rapide</p>
                    <p class="text-gray-400 text-xs mt-1">Creez-en une pour gagner du temps</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Modal Create/Edit --}}
<div id="canned-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg relative">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 id="modal-title" class="text-lg font-semibold text-gray-900">Nouvelle reponse rapide</h3>
            </div>
            <form id="canned-form" class="px-6 py-5 space-y-4">
                <input type="hidden" id="canned-id" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Raccourci</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-mono text-sm">/</span>
                        <input type="text" id="canned-code"
                               class="w-full pl-7 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="bonjour" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contenu du message</label>
                    <textarea id="canned-content" rows="5"
                              class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm resize-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                              placeholder="Bonjour ! Comment puis-je vous aider aujourd'hui ?" required></textarea>
                </div>
            </form>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2">
                <button onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Annuler</button>
                <button onclick="saveResponse()" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition shadow-sm">Enregistrer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const TOKEN = document.querySelector('meta[name="csrf-token"]').content;

function openModal(resp = null) {
    document.getElementById('canned-modal').classList.remove('hidden');
    if (resp) {
        document.getElementById('modal-title').textContent = 'Modifier la reponse';
        document.getElementById('canned-id').value = resp.id;
        document.getElementById('canned-code').value = resp.short_code;
        document.getElementById('canned-content').value = resp.content;
    } else {
        document.getElementById('modal-title').textContent = 'Nouvelle reponse rapide';
        document.getElementById('canned-id').value = '';
        document.getElementById('canned-code').value = '';
        document.getElementById('canned-content').value = '';
    }
}

function closeModal() {
    document.getElementById('canned-modal').classList.add('hidden');
}

async function saveResponse() {
    const id = document.getElementById('canned-id').value;
    const short_code = document.getElementById('canned-code').value.trim();
    const content = document.getElementById('canned-content').value.trim();
    if (!short_code || !content) return;

    try {
        const url = id ? `/ajax/canned-responses/${id}` : '/ajax/canned-responses';
        const method = id ? 'PUT' : 'POST';
        const r = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
            body: JSON.stringify({ short_code, content }),
        });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        location.reload();
    } catch(e) {
        console.error('Save:', e);
        alert('Erreur lors de la sauvegarde');
    }
}

async function deleteResponse(id) {
    if (!confirm('Supprimer cette reponse rapide ?')) return;
    try {
        await fetch(`/ajax/canned-responses/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': TOKEN },
        });
        location.reload();
    } catch(e) {
        console.error('Delete:', e);
    }
}
</script>
@endpush
