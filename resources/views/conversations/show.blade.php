@extends('layouts.app')
@section('title', 'Conversation #' . $conversation->id)

@section('content')
<div class="flex h-[calc(100vh-3.5rem)] md:h-screen">

    {{-- Zone chat --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Header --}}
        <div class="h-16 px-5 bg-white border-b border-gray-200 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <a href="{{ route('conversations.index') }}" class="text-gray-400 hover:text-gray-600 md:hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>

                @if($contact && ($contact['thumbnail'] ?? null))
                    <img src="{{ $contact['thumbnail'] }}" class="w-9 h-9 rounded-full" alt="">
                @else
                    <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-semibold">
                        {{ mb_strtoupper(mb_substr($conversation->contactName, 0, 1)) }}
                    </div>
                @endif

                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-gray-900 truncate">{{ $conversation->contactName }}</h2>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span>{{ $conversation->contactPhone ?? '' }}</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $conversation->statusBadgeClass() }}">
                            {{ ucfirst($conversation->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <select id="assign-select"
                        class="text-xs border-gray-200 rounded-lg px-2.5 py-1.5 bg-gray-50 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Assigner...</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent['id'] }}" {{ ($assignee['id'] ?? null) == $agent['id'] ? 'selected' : '' }}>
                            {{ $agent['name'] }}
                        </option>
                    @endforeach
                </select>

                @if($conversation->status === 'resolved')
                    <button onclick="toggleStatus('reopen')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-green-50 text-green-700 border border-green-200 rounded-lg hover:bg-green-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reouvrir
                    </button>
                @else
                    <button onclick="toggleStatus('resolve')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Resoudre
                    </button>
                @endif
            </div>
        </div>

        {{-- Messages --}}
        <div id="messages-container" class="flex-1 overflow-y-auto px-5 py-4 space-y-3 bg-gray-50 scrollbar-thin">
            @foreach($messages as $msg)
                @php
                    $isOutgoing = ($msg['message_type'] ?? 0) == 1;
                    $isActivity = ($msg['message_type'] ?? 0) == 2;
                    $isPrivate  = $msg['private'] ?? false;
                @endphp

                @if($isActivity)
                    <div class="flex justify-center">
                        <span class="text-[11px] text-gray-400 bg-white border border-gray-100 px-3 py-1 rounded-full shadow-sm">
                            {{ $msg['content'] ?? '' }}
                        </span>
                    </div>
                @else
                    <div class="{{ $isOutgoing ? 'message-outgoing' : 'message-incoming' }}">
                        <div class="max-w-[75%] {{ $isPrivate ? 'bg-amber-50 border border-amber-200' : ($isOutgoing ? 'bg-primary-600 text-white' : 'bg-white border border-gray-200') }} rounded-2xl {{ $isOutgoing ? 'rounded-br-md' : 'rounded-bl-md' }} px-4 py-2.5 shadow-sm">
                            @if($isPrivate)
                                <p class="text-[10px] font-semibold text-amber-600 mb-0.5">Note privee</p>
                            @endif
                            <p class="text-[13px] leading-relaxed whitespace-pre-wrap">{{ $msg['content'] ?? '' }}</p>
                            <p class="text-[10px] mt-1.5 {{ $isPrivate ? 'text-amber-400' : ($isOutgoing ? 'text-primary-200' : 'text-gray-400') }}">
                                @if($msg['created_at'] ?? null)
                                    {{ is_numeric($msg['created_at']) ? date('H:i', $msg['created_at']) : \Carbon\Carbon::parse($msg['created_at'])->format('H:i') }}
                                @endif
                                @if($isOutgoing && isset($msg['sender']['name']))
                                    &middot; {{ $msg['sender']['name'] }}
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Zone de saisie --}}
        <div class="bg-white border-t border-gray-200 px-5 py-3 flex-shrink-0">
            <form id="message-form" class="flex items-end gap-3">
                <div class="flex-1">
                    <textarea id="message-input"
                              rows="1"
                              placeholder="Ecrivez votre message..."
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm resize-none bg-gray-50 placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 focus:bg-white transition"
                              onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('message-form').dispatchEvent(new Event('submit'))}"></textarea>
                </div>
                <div class="flex items-center gap-1.5 pb-0.5">
                    <button type="button" onclick="sendPrivateNote()"
                            title="Note privee"
                            class="w-9 h-9 flex items-center justify-center rounded-lg text-amber-500 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </button>
                    <button type="submit"
                            class="w-9 h-9 flex items-center justify-center rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar contact --}}
    <div class="w-72 bg-white border-l border-gray-200 flex-shrink-0 overflow-y-auto hidden xl:flex xl:flex-col">
        <div class="p-5 border-b border-gray-100">
            <div class="flex flex-col items-center text-center">
                @if($contact && ($contact['thumbnail'] ?? null))
                    <img src="{{ $contact['thumbnail'] }}" class="w-16 h-16 rounded-full mb-3" alt="">
                @else
                    <div class="w-16 h-16 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xl font-bold mb-3">
                        {{ mb_strtoupper(mb_substr($conversation->contactName, 0, 1)) }}
                    </div>
                @endif
                <h3 class="font-semibold text-gray-900">{{ $contact['name'] ?? $conversation->contactName }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ $contact['phone_number'] ?? $conversation->contactPhone ?? '' }}</p>
            </div>
        </div>

        @if($contact)
            <div class="p-5 space-y-4 text-sm">
                @if($contact['email'] ?? null)
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Email</p>
                    <p class="text-gray-700 mt-0.5">{{ $contact['email'] }}</p>
                </div>
                @endif

                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Conversation</p>
                    <p class="text-gray-700 mt-0.5 font-mono">#{{ $conversation->id }}</p>
                </div>

                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Creee le</p>
                    <p class="text-gray-700 mt-0.5">{{ $conversation->createdAt }}</p>
                </div>

                @if($conversation->assigneeName)
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Assignee a</p>
                    <p class="text-gray-700 mt-0.5">{{ $conversation->assigneeName }}</p>
                </div>
                @endif

                @if(count($conversation->labels) > 0)
                <div>
                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1.5">Labels</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($conversation->labels as $label)
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs">{{ $label }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const CID = {{ $conversation->id }};
    const POLL = {{ config('chatwoot.polling_interval', 4000) }};
    const TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    const container = document.getElementById('messages-container');
    const form = document.getElementById('message-form');
    const input = document.getElementById('message-input');
    const assignSel = document.getElementById('assign-select');

    let lastId = {{ collect($messages)->max('id') ?? 0 }};

    // Polling
    setInterval(async () => {
        try {
            const r = await fetch(`/ajax/conversations/${CID}/poll?last_message_id=${lastId}`);
            const d = await r.json();
            if (d.count > 0) {
                d.messages.forEach(m => { appendMsg(m); lastId = Math.max(lastId, m.id); });
                scroll();
            }
        } catch(e) { console.error('Poll:', e); }
    }, POLL);

    // Envoi message
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const c = input.value.trim();
        if (!c) return;
        input.value = '';
        input.focus();
        try {
            const r = await fetch(`/ajax/conversations/${CID}/messages`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ content: c, is_private: false }),
            });
            if (!r.ok) { const err = await r.text(); console.error('Send HTTP error:', r.status, err); alert('Erreur envoi: ' + r.status); return; }
            const m = await r.json();
            console.log('Message sent:', m);
            appendMsg(m);
            lastId = Math.max(lastId, m.id);
            scroll();
        } catch(e) { console.error('Send:', e); alert('Erreur envoi'); }
    });

    // Note privee
    window.sendPrivateNote = async function() {
        const c = input.value.trim();
        if (!c) return;
        input.value = '';
        input.focus();
        try {
            const r = await fetch(`/ajax/conversations/${CID}/messages`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ content: c, is_private: true }),
            });
            if (!r.ok) return;
            const m = await r.json();
            appendMsg(m);
            lastId = Math.max(lastId, m.id);
            scroll();
        } catch(e) { console.error('Note:', e); }
    };

    // Toggle status
    window.toggleStatus = async function(action) {
        try {
            await fetch(`/ajax/conversations/${CID}/status`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ action }),
            });
            location.reload();
        } catch(e) { console.error('Status:', e); }
    };

    // Assign
    assignSel.addEventListener('change', async function() {
        if (!this.value) return;
        try {
            await fetch(`/ajax/conversations/${CID}/assign`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ agent_id: parseInt(this.value) }),
            });
        } catch(e) { console.error('Assign:', e); }
    });

    // Helpers
    function appendMsg(m) {
        const out = m.message_type === 1;
        const act = m.message_type === 2;
        const prv = m.private || false;
        const div = document.createElement('div');

        if (act) {
            div.className = 'flex justify-center';
            div.innerHTML = `<span class="text-[11px] text-gray-400 bg-white border border-gray-100 px-3 py-1 rounded-full shadow-sm">${esc(m.content||'')}</span>`;
        } else {
            div.className = out ? 'message-outgoing' : 'message-incoming';
            const bg = prv ? 'bg-amber-50 border border-amber-200' : (out ? 'bg-primary-600 text-white' : 'bg-white border border-gray-200');
            const corner = out ? 'rounded-br-md' : 'rounded-bl-md';
            const tc = prv ? 'text-amber-400' : (out ? 'text-primary-200' : 'text-gray-400');
            const time = m.created_at ? new Date(m.created_at * 1000).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}) : '';
            const sender = (out && m.sender?.name) ? ` &middot; ${esc(m.sender.name)}` : '';
            const prvTag = prv ? '<p class="text-[10px] font-semibold text-amber-600 mb-0.5">Note privee</p>' : '';
            div.innerHTML = `<div class="max-w-[75%] ${bg} rounded-2xl ${corner} px-4 py-2.5 shadow-sm">${prvTag}<p class="text-[13px] leading-relaxed whitespace-pre-wrap">${esc(m.content||'')}</p><p class="text-[10px] mt-1.5 ${tc}">${time}${sender}</p></div>`;
        }
        container.appendChild(div);
    }

    function scroll() { container.scrollTop = container.scrollHeight; }
    function esc(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    scroll();
});
</script>
@endpush
