{{-- Onglet Contacts de la campagne --}}
<div class="space-y-4">
    {{-- Barre d'actions --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center gap-3 flex-wrap">
            <div class="flex-1 relative min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="contact-search-input" placeholder="Rechercher un contact a ajouter..."
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                       autocomplete="off">
                <div id="contact-search-results" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-20 max-h-60 overflow-y-auto"></div>
            </div>
            <button onclick="openBulkAddModal()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Ajouter des contacts
            </button>
            <button onclick="addAllContacts()" id="add-all-btn"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajouter tous les contacts
            </button>
        </div>
    </div>

    {{-- Liste des contacts de la campagne --}}
    @if($campaign->contacts->count())
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Contact</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Telephone</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Email</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($campaign->contacts as $contact)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-semibold">
                                {{ $contact->initials() }}
                            </div>
                            <span class="font-medium text-gray-900">{{ $contact->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-600 font-mono text-xs">{{ $contact->phone_number }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $contact->email ?? '-' }}</td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if($campaign->status === 'draft' || $campaign->status === 'active')
                            <button onclick="sendToContact({{ $contact->id }})" title="Envoyer individuellement"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-emerald-500 hover:bg-emerald-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            </button>
                            @endif
                            <button onclick="detachContact({{ $contact->id }})" title="Retirer de la campagne"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p class="text-sm text-gray-500">Aucun contact dans cette campagne</p>
        <p class="text-xs text-gray-400 mt-1">Cliquez sur "Ajouter des contacts" ou "Ajouter tous" pour commencer</p>
    </div>
    @endif
</div>

{{-- Modale selection multiple de contacts --}}
<div id="bulk-add-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden max-h-[85vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <h3 class="font-semibold text-gray-900">Selectionner des contacts</h3>
            <button onclick="closeBulkAddModal()" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Recherche dans la modale --}}
        <div class="px-6 py-3 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="bulk-search-input" placeholder="Filtrer..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                </div>
                <button onclick="toggleSelectAll()" class="px-3 py-2 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition whitespace-nowrap">
                    Tout cocher
                </button>
            </div>
        </div>

        {{-- Liste scrollable --}}
        <div class="flex-1 overflow-y-auto px-6 py-2" id="bulk-contact-list">
            <div class="text-center py-8 text-sm text-gray-400">Chargement...</div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between flex-shrink-0">
            <span id="bulk-selected-count" class="text-sm text-gray-500">0 selectionne(s)</span>
            <div class="flex items-center gap-3">
                <button onclick="closeBulkAddModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">Annuler</button>
                <button onclick="confirmBulkAdd()" id="bulk-add-confirm-btn" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition shadow-sm">
                    Ajouter la selection
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    const existingIds = @json($campaign->contacts->pluck('id'));
    let allContacts = [];
    let selectedIds = new Set();

    // ═══ Recherche rapide (barre principale) ═══
    const input = document.getElementById('contact-search-input');
    const results = document.getElementById('contact-search-results');
    let searchTimeout = null;

    input.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const q = this.value.trim();
        if (q.length < 2) { results.classList.add('hidden'); return; }

        searchTimeout = setTimeout(async () => {
            try {
                const r = await fetch(`{{ route('ajax.campagnes.contacts.search') }}?q=${encodeURIComponent(q)}`);
                const contacts = await r.json();

                if (!contacts.length) {
                    results.innerHTML = '<div class="px-4 py-3 text-sm text-gray-400">Aucun contact trouve</div>';
                    results.classList.remove('hidden');
                    return;
                }

                results.innerHTML = contacts.map(c => {
                    const isAdded = existingIds.includes(c.id);
                    return `<div class="px-4 py-2.5 hover:bg-gray-50 flex items-center justify-between cursor-pointer transition ${isAdded ? 'opacity-50' : ''}" data-id="${c.id}">
                        <div>
                            <p class="text-sm font-medium text-gray-900">${esc(c.name)}</p>
                            <p class="text-xs text-gray-500">${esc(c.phone_number)}</p>
                        </div>
                        ${isAdded
                            ? '<span class="text-xs text-gray-400">Deja ajoute</span>'
                            : '<button class="add-contact-btn text-xs font-medium text-emerald-600 hover:text-emerald-700" data-id="' + c.id + '">+ Ajouter</button>'
                        }
                    </div>`;
                }).join('');
                results.classList.remove('hidden');

                results.querySelectorAll('.add-contact-btn').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        await attachIds([parseInt(btn.dataset.id)]);
                    });
                });
            } catch(err) {}
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.classList.add('hidden');
        }
    });

    // ═══ Ajouter TOUS les contacts ═══
    window.addAllContacts = async function() {
        if (!confirm('Ajouter tous les contacts de la base a cette campagne ?')) return;
        const btn = document.getElementById('add-all-btn');
        btn.disabled = true; btn.textContent = 'Ajout en cours...';
        try {
            const r = await fetch(`{{ route('ajax.campagnes.contacts.search') }}?q=&all=1`);
            const contacts = await r.json();
            const ids = contacts.map(c => c.id).filter(id => !existingIds.includes(id));
            if (ids.length === 0) {
                alert('Tous les contacts sont deja dans la campagne');
                btn.disabled = false; btn.innerHTML = restoreAddAllBtn();
                return;
            }
            await attachIds(ids);
        } catch(err) { alert('Erreur'); btn.disabled = false; btn.innerHTML = restoreAddAllBtn(); }
    };

    function restoreAddAllBtn() {
        return '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Ajouter tous les contacts';
    }

    // ═══ Modale selection multiple ═══
    window.openBulkAddModal = async function() {
        document.getElementById('bulk-add-modal').classList.remove('hidden');
        selectedIds.clear();
        updateSelectedCount();

        const list = document.getElementById('bulk-contact-list');
        list.innerHTML = '<div class="text-center py-8 text-sm text-gray-400">Chargement...</div>';

        try {
            const r = await fetch(`{{ route('ajax.campagnes.contacts.search') }}?q=`);
            allContacts = await r.json();
            renderBulkList(allContacts);
        } catch(err) {
            list.innerHTML = '<div class="text-center py-8 text-sm text-red-400">Erreur de chargement</div>';
        }
    };

    window.closeBulkAddModal = function() {
        document.getElementById('bulk-add-modal').classList.add('hidden');
    };

    document.getElementById('bulk-add-modal').addEventListener('click', function(e) {
        if (e.target === this) closeBulkAddModal();
    });

    // Filtre dans la modale
    document.getElementById('bulk-search-input').addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        const filtered = allContacts.filter(c =>
            c.name.toLowerCase().includes(q) || c.phone_number.includes(q)
        );
        renderBulkList(filtered);
    });

    function renderBulkList(contacts) {
        const list = document.getElementById('bulk-contact-list');
        if (!contacts.length) {
            list.innerHTML = '<div class="text-center py-8 text-sm text-gray-400">Aucun contact</div>';
            return;
        }

        list.innerHTML = contacts.map(c => {
            const isAdded = existingIds.includes(c.id);
            const isChecked = selectedIds.has(c.id);
            const initial = (c.name || '?').charAt(0).toUpperCase();
            return `<label class="flex items-center gap-3 px-2 py-2.5 rounded-lg hover:bg-gray-50 cursor-pointer transition ${isAdded ? 'opacity-40' : ''}">
                <input type="checkbox" class="bulk-cb w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 transition"
                       data-id="${c.id}" ${isChecked ? 'checked' : ''} ${isAdded ? 'disabled' : ''}>
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-semibold flex-shrink-0">${initial}</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${esc(c.name)}</p>
                    <p class="text-xs text-gray-500">${esc(c.phone_number)}${c.email ? ' - ' + esc(c.email) : ''}</p>
                </div>
                ${isAdded ? '<span class="text-[10px] text-gray-400 flex-shrink-0">Deja ajoute</span>' : ''}
            </label>`;
        }).join('');

        list.querySelectorAll('.bulk-cb').forEach(cb => {
            cb.addEventListener('change', () => {
                const id = parseInt(cb.dataset.id);
                if (cb.checked) selectedIds.add(id);
                else selectedIds.delete(id);
                updateSelectedCount();
            });
        });
    }

    window.toggleSelectAll = function() {
        const checkboxes = document.querySelectorAll('#bulk-contact-list .bulk-cb:not(:disabled)');
        const allChecked = [...checkboxes].every(cb => cb.checked);
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
            const id = parseInt(cb.dataset.id);
            if (!allChecked) selectedIds.add(id);
            else selectedIds.delete(id);
        });
        updateSelectedCount();
    };

    function updateSelectedCount() {
        document.getElementById('bulk-selected-count').textContent = `${selectedIds.size} selectionne(s)`;
    }

    window.confirmBulkAdd = async function() {
        const ids = [...selectedIds];
        if (ids.length === 0) { alert('Selectionnez au moins un contact'); return; }
        const btn = document.getElementById('bulk-add-confirm-btn');
        btn.disabled = true; btn.textContent = 'Ajout...';
        await attachIds(ids);
    };

    // ═══ Fonction commune d'attachement ═══
    async function attachIds(ids) {
        try {
            const r = await fetch('{{ route("ajax.campagnes.attachContacts", $campaign) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify({ contact_ids: ids }),
            });
            const res = await r.json();
            if (res.success) location.reload();
            else alert(res.message || 'Erreur');
        } catch(err) { alert('Erreur reseau'); }
    }

    function esc(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
})();
</script>
@endpush
