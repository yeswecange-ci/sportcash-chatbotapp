{{-- Modale envoi immediat --}}
<div id="send-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Envoyer la campagne</h3>
            <button onclick="closeSendModal()" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5">
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-amber-800">Envoi immediat</p>
                        <p class="text-xs text-amber-600 mt-1">
                            Le template <strong>{{ $campaign->template_name }}</strong> sera envoye a
                            <strong>{{ $campaign->contacts->count() }} contact(s)</strong>.
                            Cette action est irreversible.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <button onclick="closeSendModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">Annuler</button>
            <button onclick="confirmSend()" id="confirm-send-btn" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition shadow-sm">
                Confirmer l'envoi
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.openSendModal = function() { document.getElementById('send-modal').classList.remove('hidden'); };
window.closeSendModal = function() { document.getElementById('send-modal').classList.add('hidden'); };

window.confirmSend = async function() {
    const btn = document.getElementById('confirm-send-btn');
    btn.disabled = true; btn.textContent = 'Envoi en cours...';
    const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const r = await fetch('{{ route("ajax.campagnes.send", $campaign) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
        });
        const res = await r.json();
        if (res.success) {
            closeSendModal();
            alert(res.message || 'Envoi lance !');
            location.reload();
        } else {
            alert(res.message || 'Erreur');
        }
    } catch(err) { alert('Erreur reseau'); }
    btn.disabled = false; btn.textContent = "Confirmer l'envoi";
};

document.getElementById('send-modal').addEventListener('click', function(e) {
    if (e.target === this) closeSendModal();
});
</script>
@endpush
