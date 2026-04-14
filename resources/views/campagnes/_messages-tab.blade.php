{{-- Onglet Historique des messages --}}
<div>
    @if($campaign->messages->count())
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Contact</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Telephone</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Statut</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Twilio SID</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Erreur</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Envoye le</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($campaign->messages as $msg)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-semibold">
                                {{ $msg->contact ? $msg->contact->initials() : '?' }}
                            </div>
                            <span class="font-medium text-gray-900">{{ $msg->contact->name ?? 'Inconnu' }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $msg->contact->phone_number ?? '-' }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $msg->statusBadgeClass() }}">
                            {{ $msg->statusLabel() }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-400 font-mono text-[10px]">{{ $msg->twilio_message_sid ? substr($msg->twilio_message_sid, 0, 20) . '...' : '-' }}</td>
                    <td class="px-5 py-3">
                        @if($msg->error_message)
                        <span class="text-xs text-red-500 truncate max-w-[200px] block" title="{{ $msg->error_message }}">{{ Str::limit($msg->error_message, 40) }}</span>
                        @else
                        <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">
                        {{ $msg->sent_at ? $msg->sent_at->format('d/m/Y H:i') : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <p class="text-sm text-gray-500">Aucun message envoye</p>
        <p class="text-xs text-gray-400 mt-1">Les messages apparaitront ici apres l'envoi</p>
    </div>
    @endif
</div>
