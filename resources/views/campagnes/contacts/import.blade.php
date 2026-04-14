@extends('layouts.app')
@section('title', 'Import CSV')

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('campagnes.contacts.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Import CSV</h1>
                <p class="text-sm text-gray-500 mt-0.5">Importez des contacts depuis un fichier CSV</p>
            </div>
        </div>

        {{-- Etape 1 : Selection campagne(s) + Upload --}}
        <div id="step-upload" class="space-y-6">

            {{-- Selection campagne(s) optionnelle --}}
            @if($campaigns->count())
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-1">Ajouter a une ou plusieurs campagnes</h3>
                <p class="text-[11px] text-gray-400 mb-3">Optionnel — les contacts importes seront ajoutes aux campagnes selectionnees</p>
                <div class="space-y-1.5 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-2.5">
                    @foreach($campaigns as $campaign)
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-gray-50 cursor-pointer transition text-sm">
                        <input type="checkbox" value="{{ $campaign->id }}" class="import-campaign-cb rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                        <span class="text-gray-700">{{ $campaign->name }}</span>
                        <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded {{ $campaign->statusBadgeClass() }}">{{ $campaign->statusLabel() }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Upload fichier --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-8 text-center" id="drop-zone">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <p class="text-sm text-gray-600 mb-2">Glissez votre fichier CSV ici ou</p>
                    <label id="upload-btn" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition cursor-pointer shadow-sm">
                        <svg class="w-4 h-4 btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <svg class="w-4 h-4 spinner hidden animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span class="btn-label">Choisir un fichier</span>
                        <input type="file" id="csv-file" accept=".csv,.txt" class="hidden">
                    </label>
                    <p class="text-xs text-gray-400 mt-3">Format attendu : colonnes "name" (ou "nom") et "phone" (ou "telephone"). Colonne "email" optionnelle.</p>
                </div>
            </div>

            <div class="bg-primary-50 border border-primary-200 rounded-xl p-4">
                <h4 class="text-sm font-medium text-primary-800 mb-2">Exemple de format CSV</h4>
                <code class="block text-xs text-primary-700 bg-primary-100 rounded-lg p-3 font-mono">
                    name,phone,email<br>
                    Jean Dupont,+2250700000001,jean@example.com<br>
                    Marie Konan,+2250700000002,<br>
                    Awa Diallo,225 07 00 000 003,awa@test.ci
                </code>
            </div>
        </div>

        {{-- Etape 2 : Preview --}}
        <div id="step-preview" class="hidden space-y-6">
            {{-- Resume campagnes selectionnees --}}
            <div id="selected-campaigns-summary" class="hidden bg-primary-50 border border-primary-200 rounded-xl p-4">
                <p class="text-sm text-primary-800"><strong>Campagne(s) :</strong> <span id="selected-campaigns-names"></span></p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Apercu de l'import</h3>
                    <span id="preview-total" class="text-sm text-gray-500"></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="preview-table">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-2 font-semibold text-gray-600">Nom</th>
                                <th class="text-left px-4 py-2 font-semibold text-gray-600">Telephone</th>
                                <th class="text-left px-4 py-2 font-semibold text-gray-600">Email</th>
                            </tr>
                        </thead>
                        <tbody id="preview-body" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button onclick="resetImport()" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Retour
                </button>
                <button onclick="confirmImport()" id="confirm-import-btn" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 transition shadow-sm">
                    <svg class="w-4 h-4 spinner hidden animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="btn-label">Confirmer l'import</span>
                </button>
            </div>
        </div>

        {{-- Etape 3 : Resultat --}}
        <div id="step-result" class="hidden">
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Import termine</h3>
                <div id="result-details" class="text-sm text-gray-600 space-y-1"></div>
                <div class="flex items-center justify-center gap-3 mt-6">
                    <a href="{{ route('campagnes.contacts.index') }}" class="px-5 py-2.5 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 transition shadow-sm">
                        Voir les contacts
                    </a>
                    <button onclick="resetImport()" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Nouvel import
                    </button>
                </div>
            </div>
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
    let filePath = null;

    const fileInput = document.getElementById('csv-file');
    const dropZone = document.getElementById('drop-zone');

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

    // ═══ Campagnes selectionnees ═══
    function getSelectedCampaignIds() {
        return [...document.querySelectorAll('.import-campaign-cb:checked')].map(cb => cb.value);
    }

    function getSelectedCampaignNames() {
        return [...document.querySelectorAll('.import-campaign-cb:checked')].map(cb => {
            return cb.closest('label').querySelector('.text-gray-700').textContent.trim();
        });
    }

    // ═══ Drag & drop ═══
    ['dragenter', 'dragover'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.add('border-primary-500', 'bg-primary-50'); });
    });
    ['dragleave', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => { e.preventDefault(); dropZone.classList.remove('border-primary-500', 'bg-primary-50'); });
    });
    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length) uploadPreview(files[0]);
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) uploadPreview(fileInput.files[0]);
    });

    async function uploadPreview(file) {
        const form = new FormData();
        form.append('file', file);
        const uploadBtn = document.getElementById('upload-btn');
        btnLoad(uploadBtn);

        try {
            const r = await fetch('{{ route("ajax.campagnes.contacts.importPreview") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
                body: form,
            });
            const res = await r.json();

            if (!r.ok) {
                toast(res.message || Object.values(res.errors || {}).flat().join(', ') || 'Erreur', 'error');
                btnReset(uploadBtn);
                return;
            }

            filePath = res.file_path;
            document.getElementById('preview-total').textContent = `${res.total_rows} ligne(s) trouvee(s)`;

            const tbody = document.getElementById('preview-body');
            tbody.innerHTML = '';
            res.preview.forEach(row => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                tr.innerHTML = `<td class="px-4 py-2 text-gray-900">${row.name || '-'}</td>
                    <td class="px-4 py-2 text-gray-600 font-mono text-xs">${row.phone || '-'}</td>
                    <td class="px-4 py-2 text-gray-500">${row.email || '-'}</td>`;
                tbody.appendChild(tr);
            });

            // Afficher resume campagnes selectionnees
            const names = getSelectedCampaignNames();
            const summary = document.getElementById('selected-campaigns-summary');
            if (names.length > 0) {
                document.getElementById('selected-campaigns-names').textContent = names.join(', ');
                summary.classList.remove('hidden');
            } else {
                summary.classList.add('hidden');
            }

            document.getElementById('step-upload').classList.add('hidden');
            document.getElementById('step-preview').classList.remove('hidden');
            toast('Fichier charge, verifiez l\'apercu', 'info');
        } catch(err) {
            toast('Erreur reseau', 'error');
        }
        btnReset(uploadBtn);
    }

    window.confirmImport = async function() {
        if (!filePath) return;
        const btn = document.getElementById('confirm-import-btn');
        btnLoad(btn);

        try {
            const campaignIds = getSelectedCampaignIds();
            const payload = { file_path: filePath };
            if (campaignIds.length) payload.campaign_ids = campaignIds;

            const r = await fetch('{{ route("ajax.campagnes.contacts.importConfirm") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const res = await r.json();

            const details = document.getElementById('result-details');
            let html = `
                <p><strong>${res.imported}</strong> contact(s) importe(s)</p>
                <p><strong>${res.skipped}</strong> contact(s) ignore(s) (doublons ou invalides)</p>
            `;
            if (campaignIds.length) {
                html += `<p class="text-primary-600 mt-1">Ajoute(s) a ${campaignIds.length} campagne(s)</p>`;
            }
            if (res.errors?.length) {
                html += '<p class="text-red-500 mt-2">' + res.errors.join('<br>') + '</p>';
            }
            details.innerHTML = html;

            document.getElementById('step-preview').classList.add('hidden');
            document.getElementById('step-result').classList.remove('hidden');
            toast(`${res.imported} contact(s) importe(s) avec succes`, 'success');
        } catch(err) {
            toast('Erreur reseau', 'error');
        }
        btnReset(btn);
    };

    window.resetImport = function() {
        filePath = null;
        fileInput.value = '';
        document.querySelectorAll('.import-campaign-cb').forEach(cb => cb.checked = false);
        document.getElementById('step-upload').classList.remove('hidden');
        document.getElementById('step-preview').classList.add('hidden');
        document.getElementById('step-result').classList.add('hidden');
    };
})();
</script>
@endpush
