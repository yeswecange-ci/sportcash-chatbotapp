{{-- Statistiques de delivrabilite --}}
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
    @php
        $statItems = [
            ['label' => 'Total', 'key' => 'total', 'color' => 'gray', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['label' => 'En file', 'key' => 'queued', 'color' => 'gray', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Envoyes', 'key' => 'sent', 'color' => 'blue', 'icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
            ['label' => 'Delivres', 'key' => 'delivered', 'color' => 'green', 'icon' => 'M5 13l4 4L19 7'],
            ['label' => 'Lus', 'key' => 'read', 'color' => 'indigo', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
            ['label' => 'Echoues', 'key' => 'failed', 'color' => 'red', 'icon' => 'M6 18L18 6M6 6l12 12'],
            ['label' => 'Non delivres', 'key' => 'undelivered', 'color' => 'orange', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z'],
        ];
    @endphp

    @foreach($statItems as $item)
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <div class="w-8 h-8 mx-auto mb-2 rounded-full bg-{{ $item['color'] }}-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-{{ $item['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/>
            </svg>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $stats[$item['key']] ?? 0 }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $item['label'] }}</p>
    </div>
    @endforeach
</div>

@if(($stats['total'] ?? 0) > 0)
<div class="flex justify-end mb-4">
    <button id="refresh-btn" onclick="refreshStatuses()"
            class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Rafraichir les statuts
    </button>
</div>
@endif
