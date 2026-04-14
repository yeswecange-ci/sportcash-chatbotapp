@extends('layouts.app')
@section('title', 'Contacts Campagnes')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Contacts</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $contacts->total() }} contact(s) disponibles pour vos campagnes</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="importFromChatwoot()" id="chatwoot-import-btn"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-blue-700 text-sm font-medium rounded-lg border border-blue-200 hover:bg-blue-50 transition">
                    <svg class="w-4 h-4 spinner hidden animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <svg class="w-4 h-4 btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span class="btn-label">Sync Plateforme</span>
                </button>
                <a href="{{ route('campagnes.contacts.import') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import CSV
                </a>
                <button onclick="openAddContactModal()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-500 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau
                </button>
            </div>
        </div>

        {{-- Recherche --}}
        <div class="mb-5">
            <form method="GET" class="relative max-w-sm" id="contacts-search-form">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" id="contacts-search-input" value="{{ request('search') }}" placeholder="Rechercher un contact..."
                       autocomplete="off"
                       class="w-full pl-10 pr-10 py-2.5 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm">
                <svg id="contacts-search-spinner" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-primary-400 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            </form>
        </div>

        {{-- Grille contacts --}}
        @if($contacts->count())
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($contacts as $contact)
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm hover:border-primary-200 transition group" id="contact-card-{{ $contact->id }}">
                <div class="flex items-start gap-3">
                    {{-- Avatar --}}
                    <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold flex-shrink-0">
                        {{ $contact->initials() }}
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 text-sm truncate">{{ $contact->name }}</p>
                        <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $contact->phone_number }}</p>
                        @if($contact->email)
                        <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $contact->email }}</p>
                        @endif

                        {{-- Tags --}}
                        <div class="flex items-center gap-2 mt-2">
                            @if($contact->chatwoot_contact_id)
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-600">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Synchro
                            </span>
                            @else
                            <button onclick="syncContact({{ $contact->id }}, this)" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Sync
                            </button>
                            @endif
                            <span class="text-[10px] text-gray-300">{{ $contact->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition flex-shrink-0">
                        <button onclick="editContact({{ $contact->id }}, '{{ addslashes($contact->name) }}', '{{ $contact->phone_number }}', '{{ $contact->email }}')"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition" title="Modifier">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button onclick="openDeleteModal({{ $contact->id }}, '{{ addslashes($contact->name) }}')"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500 transition" title="Supprimer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-5">{{ $contacts->withQueryString()->links() }}</div>

        @else
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-primary-50 flex items-center justify-center">
                <svg class="w-7 h-7 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-1">Aucun contact</h3>
            <p class="text-sm text-gray-400 mb-5">Commencez par importer vos contacts depuis la plateforme ou un fichier CSV</p>
            <div class="flex items-center justify-center gap-3">
                <button onclick="importFromChatwoot()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Importer depuis la plateforme
                </button>
                <a href="{{ route('campagnes.contacts.import') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Import CSV
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Modale ajout/edition contact --}}
<div id="contact-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[420px] mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 id="contact-modal-title" class="font-semibold text-gray-900">Nouveau contact</h3>
            <button onclick="closeContactModal()" class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="contact-form" class="px-6 py-5 space-y-4">
            <input type="hidden" id="contact-edit-id" value="">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                <input type="text" id="contact-name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telephone *</label>
                <input type="text" id="contact-phone" required placeholder="+225XXXXXXXXXX" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="contact-email" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Campagne(s)</label>
                <div id="contact-campaigns" class="space-y-1.5 max-h-36 overflow-y-auto border border-gray-200 rounded-lg p-2.5">
                    @forelse($campaigns as $campaign)
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-gray-50 cursor-pointer transition text-sm">
                        <input type="checkbox" value="{{ $campaign->id }}" class="campaign-checkbox rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                        <span class="text-gray-700">{{ $campaign->name }}</span>
                        <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded {{ $campaign->statusBadgeClass() }}">{{ $campaign->statusLabel() }}</span>
                    </label>
                    @empty
                    <p class="text-xs text-gray-400 text-center py-2">Aucune campagne disponible</p>
                    @endforelse
                </div>
                <p class="text-[11px] text-gray-400 mt-1">Optionnel — le contact sera ajoute aux campagnes selectionnees</p>
            </div>
        </form>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <button onclick="closeContactModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Annuler</button>
            <button onclick="saveContact()" id="save-contact-btn" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 transition shadow-sm">
                <svg class="w-4 h-4 spinner hidden animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span class="btn-label">Enregistrer</span>
            </button>
        </div>
    </div>
</div>

{{-- Modale confirmation suppression --}}
<div id="delete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[420px] mx-4 overflow-hidden">
        <div class="px-6 py-5 text-center">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Supprimer le contact</h3>
            <p class="text-sm text-gray-500">Voulez-vous supprimer <strong id="delete-contact-name"></strong> ? Cette action est irreversible.</p>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Annuler</button>
            <button onclick="confirmDelete()" id="confirm-delete-btn" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition">
                <svg class="w-4 h-4 spinner hidden animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span class="btn-label">Supprimer</span>
            </button>
        </div>
    </div>
</div>

{{-- Toast container --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2"></div>
@endsection

@push('scripts')
<script>
(function() {
    const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    let deleteContactId = null;

    // ═══ Toast ═══
    function toast(msg, type = 'info') {
        const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-primary-500' };
        const icons = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
            error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
            info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        };
        const el = document.createElement('div');
        el.className = `flex items-center gap-3 px-4 py-3 rounded-xl text-white text-sm shadow-lg ${colors[type] || colors.info} animate-slide-in`;
        el.innerHTML = `<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">${icons[type] || icons.info}</svg><span>${msg}</span>`;
        document.getElementById('toast-container').appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(() => el.remove(), 300); }, 5000);
    }

    function btnLoad(btn) { btn.disabled = true; btn.querySelector('.spinner')?.classList.remove('hidden'); btn.querySelector('.btn-icon')?.classList.add('hidden'); }
    function btnReset(btn) { btn.disabled = false; btn.querySelector('.spinner')?.classList.add('hidden'); btn.querySelector('.btn-icon')?.classList.remove('hidden'); }

    // ═══ Import Chatwoot ═══
    window.importFromChatwoot = async function() {
        const btn = document.getElementById('chatwoot-import-btn');
        btnLoad(btn);
        try {
            const r = await fetch('{{ route("ajax.campagnes.contacts.importChatwoot") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
            });
            const res = await r.json();
            if (res.success) {
                toast(res.message || 'Import termine', 'success');
                if (res.imported > 0) setTimeout(() => location.reload(), 1000);
            } else {
                toast(res.message || 'Erreur', 'error');
            }
        } catch(err) { toast('Erreur reseau', 'error'); }
        btnReset(btn);
    };

    // ═══ Modale contact ═══
    window.openAddContactModal = function() {
        document.getElementById('contact-modal-title').textContent = 'Nouveau contact';
        document.getElementById('contact-edit-id').value = '';
        document.getElementById('contact-name').value = '';
        document.getElementById('contact-phone').value = '';
        document.getElementById('contact-email').value = '';
        document.querySelectorAll('.campaign-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('contact-modal').classList.remove('hidden');
    };

    window.editContact = function(id, name, phone, email) {
        document.getElementById('contact-modal-title').textContent = 'Modifier le contact';
        document.getElementById('contact-edit-id').value = id;
        document.getElementById('contact-name').value = name;
        document.getElementById('contact-phone').value = phone;
        document.getElementById('contact-email').value = email || '';
        document.querySelectorAll('.campaign-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('contact-modal').classList.remove('hidden');
    };

    window.closeContactModal = function() { document.getElementById('contact-modal').classList.add('hidden'); };

    window.saveContact = async function() {
        const id = document.getElementById('contact-edit-id').value;
        const btn = document.getElementById('save-contact-btn');
        const campaignIds = [...document.querySelectorAll('.campaign-checkbox:checked')].map(cb => cb.value);
        const data = {
            name: document.getElementById('contact-name').value,
            phone_number: document.getElementById('contact-phone').value,
            email: document.getElementById('contact-email').value || null,
            campaign_ids: campaignIds.length ? campaignIds : undefined,
        };
        if (!data.name || !data.phone_number) { toast('Nom et telephone requis', 'error'); return; }

        const url = id ? `/ajax/campagnes-contacts/${id}` : '{{ route("ajax.campagnes.contacts.store") }}';
        btnLoad(btn);
        try {
            const r = await fetch(url, {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify(data),
            });
            const res = await r.json();
            if (r.ok && res.success) {
                closeContactModal();
                toast(id ? 'Contact modifie' : 'Contact cree', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                toast(res.errors ? Object.values(res.errors).flat().join(', ') : (res.message || 'Erreur'), 'error');
            }
        } catch(err) { toast('Erreur reseau', 'error'); }
        btnReset(btn);
    };

    // ═══ Suppression avec modale ═══
    window.openDeleteModal = function(id, name) {
        deleteContactId = id;
        document.getElementById('delete-contact-name').textContent = name;
        document.getElementById('delete-modal').classList.remove('hidden');
    };

    window.closeDeleteModal = function() {
        deleteContactId = null;
        document.getElementById('delete-modal').classList.add('hidden');
    };

    window.confirmDelete = async function() {
        if (!deleteContactId) return;
        const btn = document.getElementById('confirm-delete-btn');
        btnLoad(btn);
        try {
            const r = await fetch(`/ajax/campagnes-contacts/${deleteContactId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
            });
            const res = await r.json();
            if (res.success) {
                document.getElementById('contact-card-' + deleteContactId)?.remove();
                closeDeleteModal();
                toast('Contact supprime', 'success');
            } else {
                toast(res.message || 'Erreur', 'error');
            }
        } catch(err) { toast('Erreur', 'error'); }
        btnReset(btn);
    };

    window.syncContact = async function(id, btnEl) {
        btnEl.disabled = true;
        btnEl.textContent = '...';
        try {
            const r = await fetch(`/ajax/campagnes-contacts/${id}/sync-chatwoot`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
            });
            const res = await r.json();
            if (res.success) {
                toast('Contact synchronise avec la plateforme', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                toast(res.message || 'Erreur', 'error');
                btnEl.disabled = false;
                btnEl.textContent = 'Sync';
            }
        } catch(err) {
            toast('Erreur reseau', 'error');
            btnEl.disabled = false;
            btnEl.textContent = 'Sync';
        }
    };

    // ═══ Recherche avec autocompletion (debounce) ═══
    const searchInput = document.getElementById('contacts-search-input');
    const searchForm = document.getElementById('contacts-search-form');
    const searchSpinner = document.getElementById('contacts-search-spinner');
    let searchDebounce = null;

    if (searchInput && searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            navigateSearch(searchInput.value.trim());
        });

        searchInput.addEventListener('input', function() {
            clearTimeout(searchDebounce);
            searchSpinner?.classList.remove('hidden');
            searchDebounce = setTimeout(() => {
                navigateSearch(this.value.trim());
            }, 400);
        });
    }

    function navigateSearch(q) {
        const url = new URL(window.location.href);
        if (q) {
            url.searchParams.set('search', q);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // Fermer modales en cliquant sur le backdrop
    document.getElementById('contact-modal').addEventListener('click', function(e) { if (e.target === this) closeContactModal(); });
    document.getElementById('delete-modal').addEventListener('click', function(e) { if (e.target === this) closeDeleteModal(); });
})();
</script>
@endpush
