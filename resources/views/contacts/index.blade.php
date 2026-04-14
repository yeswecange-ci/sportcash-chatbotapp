@extends('layouts.app')
@section('title', 'Contacts')

@section('content')
<div class="flex flex-col h-full overflow-hidden bg-gray-50">

    {{-- Header --}}
    <div class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Contacts</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $meta['total'] ?? count($contacts) }} contacts au total</p>
            </div>
            <button onclick="openCreateContact()" class="px-4 py-2 bg-primary-500 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouveau contact
            </button>
        </div>

        {{-- Recherche --}}
        <div class="mt-3 max-w-md">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="contact-search" value="{{ $currentSearch }}" placeholder="Rechercher par nom, email, telephone..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                       oninput="debounceSearch(this.value)">
            </div>
        </div>
    </div>

    {{-- Tableau contacts --}}
    <div class="flex-1 overflow-y-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                <tr>
                    <th class="text-left px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="text-left px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="text-left px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Telephone</th>
                    <th class="text-left px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Entreprise</th>
                    <th class="text-left px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Derniere activite</th>
                    <th class="text-right px-6 py-3 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="contacts-body" class="bg-white divide-y divide-gray-100">
                @forelse($contacts as $contact)
                <tr class="hover:bg-gray-50 transition cursor-pointer contact-row" data-id="{{ $contact['id'] }}" onclick="openContactDetail({{ $contact['id'] }})">
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-3">
                            @if(!empty($contact['thumbnail']))
                                <img src="{{ $contact['thumbnail'] }}" class="w-9 h-9 rounded-full flex-shrink-0" alt="">
                            @else
                                <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-semibold text-sm flex-shrink-0">
                                    {{ mb_substr($contact['name'] ?? '?', 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $contact['name'] ?? 'Sans nom' }}</p>
                                <p class="text-[10px] text-gray-400">#{{ $contact['id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600" onclick="event.stopPropagation()">
                        @if($contact['email'] ?? null)
                            <span>{{ $contact['email'] }}</span>
                        @else
                            <button onclick="quickEdit({{ $contact['id'] }}, 'email', this)" class="text-xs text-gray-300 hover:text-primary-500 italic transition">+ ajouter</button>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600" onclick="event.stopPropagation()">
                        @if($contact['phone_number'] ?? null)
                            <span>{{ $contact['phone_number'] }}</span>
                        @else
                            <button onclick="quickEdit({{ $contact['id'] }}, 'phone_number', this)" class="text-xs text-gray-300 hover:text-primary-500 italic transition">+ ajouter</button>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-600">{{ $contact['company']['name'] ?? '-' }}</td>
                    <td class="px-6 py-3 text-xs text-gray-400">
                        @if(!empty($contact['last_activity_at']))
                            {{ \Carbon\Carbon::parse($contact['last_activity_at'])->diffForHumans() }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right" onclick="event.stopPropagation()">
                        <div class="flex items-center justify-end gap-1">
                            @if(!empty($contact['phone_number']))
                            <button onclick="openSendTemplate({{ $contact['id'] }}, '{{ addslashes($contact['name'] ?? '') }}', '{{ addslashes($contact['phone_number']) }}')" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-md transition" title="Envoyer un template WhatsApp">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            </button>
                            @endif
                            <button onclick="openContactDetail({{ $contact['id'] }})" class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-md transition" title="Voir les détails">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            <button onclick="confirmDeleteContact({{ $contact['id'] }}, '{{ addslashes($contact['name'] ?? '') }}')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition" title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="text-sm font-medium text-gray-400">Aucun contact</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if(($meta['pages'] ?? 1) > 1)
    <div class="flex-shrink-0 bg-white border-t border-gray-200 px-6 py-3 flex items-center justify-between">
        <p class="text-xs text-gray-500">Page {{ $currentPage }} sur {{ $meta['pages'] ?? 1 }}</p>
        <div class="flex gap-2">
            @if($currentPage > 1)
                <a href="{{ route('contacts.index', ['page' => $currentPage - 1, 'q' => $currentSearch]) }}" class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-md text-gray-600 hover:bg-gray-50 transition">Precedent</a>
            @endif
            @if($currentPage < ($meta['pages'] ?? 1))
                <a href="{{ route('contacts.index', ['page' => $currentPage + 1, 'q' => $currentSearch]) }}" class="px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-md text-gray-600 hover:bg-gray-50 transition">Suivant</a>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Modal : Nouveau contact --}}
<div id="modal-create-contact" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-[420px] max-w-[90vw] overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 text-sm">Nouveau contact</h3>
            <button onclick="closeModal('modal-create-contact')" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-5 py-4 space-y-3">
            <div>
                <label class="text-xs font-medium text-gray-600">Nom *</label>
                <input type="text" id="new-contact-name" class="w-full mt-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="Nom complet">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Email</label>
                <input type="email" id="new-contact-email" class="w-full mt-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="email@exemple.com">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Telephone</label>
                <input type="text" id="new-contact-phone" class="w-full mt-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="+225 07 00 00 00">
            </div>
        </div>
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
            <button onclick="closeModal('modal-create-contact')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">Annuler</button>
            <button onclick="saveNewContact()" id="btn-save-contact" class="px-4 py-2 bg-primary-500 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition">Creer</button>
        </div>
    </div>
</div>

{{-- Modal : Envoyer template WhatsApp --}}
<div id="modal-send-template" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-[480px] max-w-[95vw] overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-sm">Envoyer un template WhatsApp</h3>
            </div>
            <button onclick="closeModal('modal-send-template')" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="px-5 py-4 space-y-4 overflow-y-auto max-h-[70vh]">
            {{-- Destinataire --}}
            <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5 border border-gray-100">
                <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-semibold text-sm flex-shrink-0" id="st-avatar">?</div>
                <div>
                    <p class="text-sm font-medium text-gray-900" id="st-name">—</p>
                    <p class="text-xs text-gray-400" id="st-phone">—</p>
                </div>
            </div>

            {{-- Sélection template --}}
            <div>
                <label class="text-xs font-medium text-gray-600">Template</label>
                <div id="st-template-loading" class="mt-1 flex items-center gap-2 text-xs text-gray-400">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Chargement des templates...
                </div>
                <select id="st-template-select" class="hidden w-full mt-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" onchange="onTemplateChange()">
                    <option value="">-- Choisir un template --</option>
                </select>
                <p id="st-template-error" class="hidden mt-1 text-xs text-red-500"></p>
            </div>

            {{-- Variables dynamiques --}}
            <div id="st-variables-section" class="hidden space-y-2">
                <label class="text-xs font-medium text-gray-600">Variables</label>
                <div id="st-variables-inputs" class="space-y-2"></div>
            </div>

            {{-- Aperçu --}}
            <div id="st-preview-section" class="hidden">
                <label class="text-xs font-medium text-gray-600">Aperçu</label>
                <div id="st-preview-body" class="mt-1 px-3 py-2.5 bg-green-50 border border-green-100 rounded-lg text-sm text-gray-700 whitespace-pre-wrap"></div>
            </div>
        </div>

        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-end gap-2 flex-shrink-0">
            <button onclick="closeModal('modal-send-template')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">Annuler</button>
            <button onclick="submitSendTemplate()" id="btn-send-template" disabled class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Envoyer
            </button>
        </div>
    </div>
</div>

{{-- Modal : Detail contact --}}
<div id="modal-contact-detail" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-[600px] max-w-[95vw] max-h-[90vh] overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <h3 class="font-semibold text-gray-900 text-sm" id="detail-title">Contact</h3>
            <div class="flex items-center gap-2">
                <span id="detail-header-actions"></span>
                <button onclick="closeModal('modal-contact-detail')" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <div id="detail-body" class="flex-1 overflow-y-auto p-5">
            <div class="p-8 text-center text-gray-400"><svg class="w-6 h-6 animate-spin mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    let searchTimer = null;

    window.debounceSearch = function(val) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const params = new URLSearchParams();
            if (val.trim()) params.set('q', val.trim());
            window.location.href = '{{ route("contacts.index") }}?' + params.toString();
        }, 500);
    };

    window.openCreateContact = function() {
        document.getElementById('new-contact-name').value = '';
        document.getElementById('new-contact-email').value = '';
        document.getElementById('new-contact-phone').value = '';
        document.getElementById('modal-create-contact').classList.remove('hidden');
        document.getElementById('new-contact-name').focus();
    };

    window.closeModal = function(id) {
        document.getElementById(id).classList.add('hidden');
    };

    window.saveNewContact = async function() {
        const name = document.getElementById('new-contact-name').value.trim();
        if (!name) { alert('Le nom est requis'); return; }
        const email = document.getElementById('new-contact-email').value.trim();
        const phone = document.getElementById('new-contact-phone').value.trim();

        const btn = document.getElementById('btn-save-contact');
        btn.disabled = true;
        btn.textContent = 'Creation...';

        try {
            const r = await fetch('/ajax/contacts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ name, email: email || null, phone_number: phone || null })
            });
            if (!r.ok) throw new Error('Erreur');
            closeModal('modal-create-contact');
            window.location.reload();
        } catch(e) {
            alert('Erreur lors de la creation: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Creer';
        }
    };

    window.confirmDeleteContact = async function(id, name) {
        if (!confirm(`Supprimer le contact "${name}" ? Cette action est irreversible.`)) return;
        try {
            const r = await fetch(`/ajax/contacts/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': TOKEN }
            });
            if (!r.ok) throw new Error('Erreur');
            const row = document.querySelector(`.contact-row[data-id="${id}"]`);
            if (row) row.remove();
        } catch(e) {
            alert('Erreur: ' + e.message);
        }
    };

    window.openContactDetail = async function(id) {
        const modal = document.getElementById('modal-contact-detail');
        const body = document.getElementById('detail-body');
        const title = document.getElementById('detail-title');
        modal.classList.remove('hidden');
        body.innerHTML = '<div class="p-8 text-center text-gray-400"><svg class="w-6 h-6 animate-spin mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>';
        document.getElementById('detail-header-actions').innerHTML = '';

        try {
            const [contactR, convsR, notesR] = await Promise.all([
                fetch(`/ajax/contacts/${id}`),
                fetch(`/ajax/contacts/${id}/conversations`),
                fetch(`/ajax/contacts/${id}/notes`)
            ]);
            const contact = await contactR.json();
            const convsData = await convsR.json();
            const notes = await notesR.json();

            const convs = convsData.payload || [];
            const notesList = notes.payload || notes || [];

            title.textContent = contact.name || 'Contact #' + id;

            // Bouton d'envoi de template dans l'en-tête du modal
            const headerActions = document.getElementById('detail-header-actions');
            if (headerActions && contact.phone_number) {
                const safeName = (contact.name || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                const safePhone = contact.phone_number.replace(/'/g, "\\'");
                headerActions.innerHTML = `<button onclick="closeModal('modal-contact-detail'); setTimeout(() => openSendTemplate(${id}, '${safeName}', '${safePhone}'), 60)"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-green-50 text-green-600 text-xs font-medium rounded-lg hover:bg-green-100 border border-green-200 transition"
                    title="Envoyer un template WhatsApp">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Envoyer template
                </button>`;
            }

            function esc(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

            let convsHtml = '';
            if (convs.length > 0) {
                convsHtml = convs.slice(0, 10).map(c => {
                    const lastMsg = c.last_non_activity_message?.content || c.messages?.[0]?.content || 'Pas de message';
                    const statusCls = c.status === 'open' ? 'bg-green-100 text-green-700' : c.status === 'resolved' ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-700';
                    return `<div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0 cursor-pointer hover:bg-gray-50 px-2 rounded" onclick="closeModal('modal-contact-detail'); window.location.href='/conversations/${c.id}'">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-medium ${statusCls}">${c.status}</span>
                        <span class="text-xs text-gray-500 truncate flex-1">${esc(lastMsg.substring(0, 60))}</span>
                        <span class="text-[10px] text-gray-300">#${c.id}</span>
                    </div>`;
                }).join('');
            } else {
                convsHtml = '<p class="text-xs text-gray-400 py-2">Aucune conversation</p>';
            }

            let notesHtml = '';
            if (Array.isArray(notesList) && notesList.length > 0) {
                notesHtml = notesList.map(n => `<div class="flex items-start gap-2 py-2 border-b border-gray-50 last:border-0 group">
                    <div class="flex-1 text-xs text-gray-600">${esc(n.content || '')}</div>
                    <button onclick="deleteNote(${id}, ${n.id}, this)" class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500 transition flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>`).join('');
            } else {
                notesHtml = '<p class="text-xs text-gray-400 py-2">Aucune note</p>';
            }

            body.innerHTML = `
                <div class="space-y-5">
                    {{-- Infos editables --}}
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span class="text-xs font-semibold text-gray-700">Modifier les informations</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-medium text-gray-500 uppercase">Nom</label>
                                <input type="text" id="edit-name" value="${esc(contact.name || '')}" placeholder="Nom complet" class="w-full mt-0.5 px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white">
                            </div>
                            <div>
                                <label class="text-[10px] font-medium text-gray-500 uppercase">Email</label>
                                <input type="email" id="edit-email" value="${esc(contact.email || '')}" placeholder="email@exemple.com" class="w-full mt-0.5 px-3 py-1.5 border ${contact.email ? 'border-gray-200' : 'border-orange-300 bg-orange-50'} rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent focus:bg-white">
                            </div>
                            <div>
                                <label class="text-[10px] font-medium text-gray-500 uppercase">Telephone</label>
                                <input type="text" id="edit-phone" value="${esc(contact.phone_number || '')}" placeholder="+225 07 00 00 00" class="w-full mt-0.5 px-3 py-1.5 border ${contact.phone_number ? 'border-gray-200' : 'border-orange-300 bg-orange-50'} rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent focus:bg-white">
                            </div>
                            <div>
                                <label class="text-[10px] font-medium text-gray-500 uppercase">Entreprise</label>
                                <input type="text" id="edit-company" value="${esc(contact.company?.name || '')}" placeholder="Nom de l'entreprise" class="w-full mt-0.5 px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white">
                            </div>
                        </div>
                        <button onclick="saveContactEdits(${id})" class="mt-3 px-4 py-1.5 bg-primary-500 text-white text-xs font-medium rounded-lg hover:bg-primary-600 transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Sauvegarder les modifications
                        </button>
                    </div>

                    {{-- Conversations --}}
                    <div>
                        <h4 class="text-xs font-semibold text-gray-700 mb-1">Conversations (${convs.length})</h4>
                        <div class="max-h-40 overflow-y-auto">${convsHtml}</div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <h4 class="text-xs font-semibold text-gray-700 mb-1">Notes</h4>
                        <div id="notes-list" class="max-h-32 overflow-y-auto">${notesHtml}</div>
                        <div class="flex gap-2 mt-2">
                            <input type="text" id="new-note-input" placeholder="Ajouter une note..." class="flex-1 px-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-primary-500" onkeydown="if(event.key==='Enter') addNote(${id})">
                            <button onclick="addNote(${id})" class="px-3 py-1.5 bg-gray-100 text-gray-600 text-xs font-medium rounded-lg hover:bg-gray-200 transition">Ajouter</button>
                        </div>
                    </div>
                </div>`;
        } catch(e) {
            body.innerHTML = '<div class="p-8 text-center text-red-400 text-sm">Erreur de chargement</div>';
        }
    };

    window.saveContactEdits = async function(id) {
        const btn = event.target.closest('button');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Sauvegarde...';

        const data = {
            name: document.getElementById('edit-name').value.trim(),
            email: document.getElementById('edit-email').value.trim() || null,
            phone_number: document.getElementById('edit-phone').value.trim() || null,
            company_name: document.getElementById('edit-company').value.trim() || null,
        };
        try {
            const r = await fetch(`/ajax/contacts/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify(data)
            });
            if (!r.ok) {
                const err = await r.json().catch(() => null);
                throw new Error(err?.message || 'Erreur serveur');
            }
            // Update table row if visible
            const row = document.querySelector(`.contact-row[data-id="${id}"]`);
            if (row) {
                const nameEl = row.querySelector('.text-sm.font-medium.text-gray-900');
                if (nameEl && data.name) nameEl.textContent = data.name;
            }
            btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Sauvegarde !';
            btn.classList.remove('bg-primary-500', 'hover:bg-primary-600');
            btn.classList.add('bg-green-500');
            setTimeout(() => window.location.reload(), 800);
        } catch(e) {
            alert('Erreur: ' + e.message);
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    };

    window.addNote = async function(contactId) {
        const input = document.getElementById('new-note-input');
        const content = input.value.trim();
        if (!content) return;
        try {
            await fetch(`/ajax/contacts/${contactId}/notes`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ content })
            });
            input.value = '';
            openContactDetail(contactId);
        } catch(e) {
            alert('Erreur: ' + e.message);
        }
    };

    window.deleteNote = async function(contactId, noteId, btn) {
        try {
            await fetch(`/ajax/contacts/${contactId}/notes/${noteId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': TOKEN }
            });
            const row = btn.closest('.flex.items-start');
            if (row) row.remove();
        } catch(e) {
            alert('Erreur: ' + e.message);
        }
    };

    // Quick inline edit for missing fields
    window.quickEdit = function(contactId, field, btn) {
        const td = btn.parentElement;
        const placeholder = field === 'email' ? 'email@exemple.com' : '+225 07 00 00 00';
        const type = field === 'email' ? 'email' : 'text';
        td.innerHTML = `<div class="flex gap-1">
            <input type="${type}" id="quick-${field}-${contactId}" placeholder="${placeholder}" class="flex-1 px-2 py-1 border border-primary-300 rounded text-xs focus:ring-1 focus:ring-primary-500" autofocus>
            <button onclick="saveQuickEdit(${contactId}, '${field}')" class="px-2 py-1 bg-primary-500 text-white text-[10px] rounded hover:bg-primary-600">OK</button>
            <button onclick="window.location.reload()" class="px-2 py-1 bg-gray-100 text-gray-500 text-[10px] rounded hover:bg-gray-200">X</button>
        </div>`;
        td.querySelector('input').focus();
        td.querySelector('input').addEventListener('keydown', e => {
            if (e.key === 'Enter') saveQuickEdit(contactId, field);
            if (e.key === 'Escape') window.location.reload();
        });
    };

    window.saveQuickEdit = async function(contactId, field) {
        const input = document.getElementById(`quick-${field}-${contactId}`);
        const val = input.value.trim();
        if (!val) return;
        try {
            const r = await fetch(`/ajax/contacts/${contactId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ [field]: val })
            });
            if (!r.ok) throw new Error('Erreur');
            input.parentElement.parentElement.innerHTML = `<span class="text-sm text-gray-600">${val}</span>`;
        } catch(e) {
            alert('Erreur: ' + e.message);
        }
    };

    // ── Send Template ─────────────────────────────────────
    let _stContactId = null;
    let _stTemplates = null; // cache

    window.openSendTemplate = async function(contactId, name, phone) {
        _stContactId = contactId;

        // Fill recipient info
        document.getElementById('st-name').textContent = name || 'Contact #' + contactId;
        document.getElementById('st-phone').textContent = phone || '—';
        document.getElementById('st-avatar').textContent = (name || '?').charAt(0).toUpperCase();

        // Reset UI
        document.getElementById('st-variables-section').classList.add('hidden');
        document.getElementById('st-preview-section').classList.add('hidden');
        document.getElementById('st-template-error').classList.add('hidden');
        document.getElementById('btn-send-template').disabled = true;

        document.getElementById('modal-send-template').classList.remove('hidden');

        // Load templates (cached)
        if (_stTemplates !== null) {
            renderTemplateSelect(_stTemplates);
            return;
        }

        document.getElementById('st-template-loading').classList.remove('hidden');
        document.getElementById('st-template-select').classList.add('hidden');

        try {
            const r = await fetch('/ajax/twilio/templates');
            if (!r.ok) throw new Error('Erreur ' + r.status);
            _stTemplates = await r.json();
            renderTemplateSelect(_stTemplates);
        } catch(e) {
            document.getElementById('st-template-loading').classList.add('hidden');
            const err = document.getElementById('st-template-error');
            err.textContent = 'Impossible de charger les templates : ' + e.message;
            err.classList.remove('hidden');
        }
    };

    function renderTemplateSelect(templates) {
        const loading = document.getElementById('st-template-loading');
        const select  = document.getElementById('st-template-select');
        loading.classList.add('hidden');

        if (!Array.isArray(templates) || templates.length === 0) {
            const err = document.getElementById('st-template-error');
            err.textContent = 'Aucun template disponible.';
            err.classList.remove('hidden');
            return;
        }

        select.innerHTML = '<option value="">-- Choisir un template --</option>';
        templates.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.sid;
            opt.dataset.body = t.body || '';
            opt.dataset.vars = JSON.stringify(t.variables || []);
            opt.dataset.name = t.friendly_name || t.sid;
            opt.textContent = t.friendly_name || t.sid;
            select.appendChild(opt);
        });
        select.classList.remove('hidden');
    }

    window.onTemplateChange = function() {
        const select = document.getElementById('st-template-select');
        const opt = select.selectedOptions[0];
        const btn = document.getElementById('btn-send-template');

        document.getElementById('st-variables-section').classList.add('hidden');
        document.getElementById('st-preview-section').classList.add('hidden');
        btn.disabled = true;

        if (!opt || !opt.value) return;

        const body = opt.dataset.body || '';
        const vars = JSON.parse(opt.dataset.vars || '[]');

        // Variables
        if (vars.length > 0) {
            const container = document.getElementById('st-variables-inputs');
            container.innerHTML = '';
            vars.forEach(v => {
                const div = document.createElement('div');
                div.innerHTML = `<label class="text-[10px] font-medium text-gray-500 uppercase">Variable {{${v}}}</label>
                    <input type="text" data-var="${v}" placeholder="Valeur pour {{${v}}}"
                        class="st-var-input w-full mt-0.5 px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        oninput="updatePreview()">`;
                container.appendChild(div);
            });
            document.getElementById('st-variables-section').classList.remove('hidden');
        }

        // Preview
        document.getElementById('st-preview-body').textContent = body;
        document.getElementById('st-preview-section').classList.remove('hidden');

        btn.disabled = (vars.length > 0); // enable only once vars filled (or no vars)
        if (vars.length === 0) btn.disabled = false;
    };

    window.updatePreview = function() {
        const select = document.getElementById('st-template-select');
        const opt = select.selectedOptions[0];
        if (!opt || !opt.value) return;

        let body = opt.dataset.body || '';
        const inputs = document.querySelectorAll('.st-var-input');
        let allFilled = true;

        inputs.forEach(inp => {
            const val = inp.value.trim();
            if (!val) allFilled = false;
            body = body.replaceAll(`{{${inp.dataset.var}}}`, val || `{{${inp.dataset.var}}}`);
        });

        document.getElementById('st-preview-body').textContent = body;
        document.getElementById('btn-send-template').disabled = !allFilled;
    };

    window.submitSendTemplate = async function() {
        if (!_stContactId) return;
        const select = document.getElementById('st-template-select');
        const opt = select.selectedOptions[0];
        if (!opt || !opt.value) return;

        const vars = JSON.parse(opt.dataset.vars || '[]');
        const variables = {};
        document.querySelectorAll('.st-var-input').forEach(inp => {
            variables[inp.dataset.var] = inp.value.trim();
        });

        const btn = document.getElementById('btn-send-template');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Envoi...';

        try {
            const r = await fetch(`/ajax/contacts/${_stContactId}/send-template`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({
                    content_sid: opt.value,
                    variables: Object.keys(variables).length > 0 ? variables : undefined,
                    template_name: opt.dataset.name,
                })
            });
            const data = await r.json();
            if (!r.ok) throw new Error(data.error || 'Erreur ' + r.status);

            // Success feedback
            btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Envoyé !';
            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            btn.classList.add('bg-green-500');
            setTimeout(() => closeModal('modal-send-template'), 1200);
        } catch(e) {
            alert('Erreur : ' + e.message);
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    };

    // Close modals on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeModal('modal-create-contact');
            closeModal('modal-contact-detail');
            closeModal('modal-send-template');
        }
    });

    // Close modals on backdrop click
    ['modal-create-contact', 'modal-contact-detail', 'modal-send-template'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', e => {
            if (e.target.id === id) closeModal(id);
        });
    });
})();
</script>
@endpush
