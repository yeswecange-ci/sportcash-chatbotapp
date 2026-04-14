{{-- Modale planification --}}
<div id="schedule-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Planifier l'envoi</h3>
            <button onclick="closeScheduleModal()" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Date et heure d'envoi</label>
                <input type="datetime-local" id="schedule-datetime"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-xs text-blue-700">
                    Le template sera envoye a <strong>{{ $campaign->contacts->count() }} contact(s)</strong> a la date choisie.
                </p>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
            <button onclick="closeScheduleModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition">Annuler</button>
            <button onclick="confirmSchedule()" id="confirm-schedule-btn" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                Planifier
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.openScheduleModal = function() {
    document.getElementById('schedule-modal').classList.remove('hidden');
    // Pre-remplir avec maintenant + 1h
    const now = new Date();
    now.setHours(now.getHours() + 1);
    document.getElementById('schedule-datetime').value = now.toISOString().slice(0, 16);
};
window.closeScheduleModal = function() { document.getElementById('schedule-modal').classList.add('hidden'); };

window.confirmSchedule = async function() {
    const dt = document.getElementById('schedule-datetime').value;
    if (!dt) { alert('Selectionnez une date'); return; }

    const btn = document.getElementById('confirm-schedule-btn');
    btn.disabled = true; btn.textContent = 'Planification...';
    const TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const r = await fetch('{{ route("ajax.campagnes.schedule", $campaign) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ scheduled_at: dt }),
        });
        const res = await r.json();
        if (res.success) {
            closeScheduleModal();
            alert(res.message || 'Campagne planifiee !');
            location.reload();
        } else {
            alert(res.message || 'Erreur');
        }
    } catch(err) { alert('Erreur reseau'); }
    btn.disabled = false; btn.textContent = 'Planifier';
};

document.getElementById('schedule-modal').addEventListener('click', function(e) {
    if (e.target === this) closeScheduleModal();
});
</script>
@endpush
