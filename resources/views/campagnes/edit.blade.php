@extends('layouts.app')
@section('title', 'Modifier ' . $campaign->name)

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('campagnes.show', $campaign) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Modifier la campagne</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $campaign->name }}</p>
            </div>
        </div>

        <form id="campaign-form" class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom de la campagne *</label>
                    <input type="text" name="name" required value="{{ $campaign->name }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition resize-none">{{ $campaign->description }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Template WhatsApp *</label>
                    <select name="template_sid" id="template-select" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                        <option value="">Selectionnez un template</option>
                        @foreach($templates as $tpl)
                        <option value="{{ $tpl['sid'] }}"
                                data-name="{{ $tpl['friendly_name'] }}"
                                data-body="{{ $tpl['body'] }}"
                                data-variables="{{ json_encode($tpl['variables']) }}"
                                {{ $campaign->template_sid === $tpl['sid'] ? 'selected' : '' }}>
                            {{ $tpl['friendly_name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>

                @if($campaign->template_body)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Template actuel</label>
                    <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                        <p class="text-sm text-primary-900 whitespace-pre-wrap">{{ $campaign->template_body }}</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('campagnes.show', $campaign) }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">Annuler</a>
                <button type="submit" id="submit-btn" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 transition shadow-sm">
                    <svg class="w-4 h-4 spinner hidden animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span class="btn-label">Enregistrer</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Toast container --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2"></div>
@endsection

@push('scripts')
<script>
(function() {
    const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

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

    function btnLoad(btn) { btn.disabled = true; btn.querySelector('.spinner')?.classList.remove('hidden'); }
    function btnReset(btn) { btn.disabled = false; btn.querySelector('.spinner')?.classList.add('hidden'); }

    document.getElementById('campaign-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;
        const btn = document.getElementById('submit-btn');
        const opt = document.getElementById('template-select').options[document.getElementById('template-select').selectedIndex];

        const data = {
            name: form.name.value,
            description: form.description.value,
            template_sid: form.template_sid.value,
            template_name: opt.dataset.name || '',
            template_body: opt.dataset.body || '',
        };

        btnLoad(btn);
        try {
            const r = await fetch('{{ route("ajax.campagnes.update", $campaign) }}', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify(data),
            });
            const res = await r.json();
            if (res.success) {
                toast('Campagne modifiee avec succes', 'success');
                setTimeout(() => window.location.href = '{{ route("campagnes.show", $campaign) }}', 800);
            } else {
                toast(res.message || 'Erreur', 'error');
                btnReset(btn);
            }
        } catch(err) {
            toast('Erreur reseau', 'error');
            btnReset(btn);
        }
    });
})();
</script>
@endpush
