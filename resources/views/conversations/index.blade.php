@extends('layouts.app')
@section('title', 'Conversations')

@section('content')
<div class="flex h-full overflow-hidden">

    {{-- ═══ LISTE DES CONVERSATIONS ═══ --}}
    <div class="w-96 border-r bg-white flex flex-col flex-shrink-0 h-full overflow-hidden">

        {{-- Header + Filtres (fixe) --}}
        <div class="p-4 border-b space-y-3 flex-shrink-0">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Conversations</h2>
                <span class="text-sm text-gray-500">{{ $meta['all_count'] ?? 0 }} total</span>
            </div>

            {{-- Recherche --}}
            <form action="{{ route('conversations.index') }}" method="GET">
                <input type="text" name="q" value="{{ $currentSearch }}"
                       placeholder="Rechercher..."
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </form>

            {{-- Tabs statut --}}
            <div class="flex gap-1">
                @php
                    $statusTabs = [
                        'open'     => ['label' => 'Ouverts',    'countKey' => 'open_count'],
                        'pending'  => ['label' => 'En attente', 'countKey' => 'pending_count'],
                        'resolved' => ['label' => 'Résolus',    'countKey' => 'resolved_count'],
                    ];
                @endphp
                @foreach($statusTabs as $status => $tabInfo)
                    <a href="{{ route('conversations.index', ['status' => $status]) }}"
                       class="flex-1 text-center py-1.5 text-xs font-medium rounded-md transition
                              {{ $currentStatus === $status ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $tabInfo['label'] }}
                        @if(isset($meta[$tabInfo['countKey']]))
                            <span class="ml-1">({{ $meta[$tabInfo['countKey']] }})</span>
                        @elseif($status === 'open' && isset($meta['all_count']))
                            <span class="ml-1">({{ $meta['all_count'] }})</span>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- Filtres rapides --}}
            <div class="flex gap-2">
                <select id="filter-assignee" onchange="applyFilters()"
                        class="flex-1 text-[11px] border-gray-200 rounded-lg px-2 py-1.5 bg-gray-50 focus:ring-2 focus:ring-primary-500">
                    @foreach(['all' => 'Tous les agents', 'me' => 'Mes conversations', 'unassigned' => 'Non assignees'] as $val => $lbl)
                        <option value="{{ $val }}" {{ $currentAssignee === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <select id="filter-label" onchange="applyFilters()"
                        class="flex-1 text-[11px] border-gray-200 rounded-lg px-2 py-1.5 bg-gray-50 focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous les labels</option>
                </select>
                <button onclick="toggleAdvancedFilters()" id="adv-filter-btn"
                        class="flex-shrink-0 px-2 py-1.5 text-[11px] font-medium rounded-lg border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 transition"
                        title="Filtres avances">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </button>
            </div>

            {{-- Panneau filtres avances (toggle) --}}
            <div id="adv-filter-panel" class="hidden border border-gray-200 rounded-lg bg-gray-50 p-3 space-y-2">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Filtres avances</span>
                    <button onclick="resetAdvancedFilters()" class="text-[10px] text-red-500 hover:text-red-600 font-medium">Reinitialiser</button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] text-gray-500 font-medium">Statut</label>
                        <select id="adv-status" class="w-full text-[11px] border-gray-200 rounded-md px-2 py-1.5 bg-white">
                            <option value="">Tous</option>
                            <option value="open">Ouvert</option>
                            <option value="pending">En attente</option>
                            <option value="resolved">Resolu</option>
                            <option value="snoozed">Reporte</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 font-medium">Agent</label>
                        <select id="adv-agent" class="w-full text-[11px] border-gray-200 rounded-md px-2 py-1.5 bg-white">
                            <option value="">Tous</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent['id'] }}">{{ $agent['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 font-medium">Equipe</label>
                        <select id="adv-team" class="w-full text-[11px] border-gray-200 rounded-md px-2 py-1.5 bg-white">
                            <option value="">Toutes</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 font-medium">Label</label>
                        <select id="adv-label" class="w-full text-[11px] border-gray-200 rounded-md px-2 py-1.5 bg-white">
                            <option value="">Tous</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 font-medium">Depuis</label>
                        <input type="date" id="adv-since" class="w-full text-[11px] border-gray-200 rounded-md px-2 py-1.5 bg-white">
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 font-medium">Jusqu'a</label>
                        <input type="date" id="adv-until" class="w-full text-[11px] border-gray-200 rounded-md px-2 py-1.5 bg-white">
                    </div>
                </div>
                <button onclick="applyAdvancedFilters()" class="w-full py-1.5 text-[11px] font-medium bg-primary-500 text-white rounded-md hover:bg-primary-600 transition">
                    Appliquer les filtres
                </button>
            </div>

            @if(auth()->user()->isAdmin())
            {{-- Auto-assignation toggle --}}
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span class="text-[11px] text-gray-500 font-medium">Auto-assignation</span>
                </div>
                <button id="auto-assign-toggle" onclick="toggleAutoAssignment()"
                        class="relative w-9 h-5 rounded-full transition-colors duration-200 bg-gray-300"
                        title="Round-robin : assigne automatiquement les nouvelles conversations aux agents en ligne">
                    <span id="auto-assign-dot" class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"></span>
                </button>
            </div>
            @endif
        </div>

        {{-- Liste (scrollable) --}}
        <div id="conversation-list" class="flex-1 overflow-y-auto scrollbar-thin">
            @forelse($conversations as $conv)
                <div class="conv-item cursor-pointer px-4 py-3 border-b hover:bg-gray-50 transition"
                     data-id="{{ $conv->id }}"
                     data-unread="{{ $conv->unreadCount }}"
                     onclick="loadConversation({{ $conv->id }})">
                    <div class="flex items-start gap-3">

                        {{-- Avatar --}}
                        <div class="flex-shrink-0 relative">
                            @if($conv->contactThumbnail)
                                <img src="{{ $conv->contactThumbnail }}" class="w-10 h-10 rounded-full" alt="">
                            @else
                                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-semibold text-sm">
                                    {{ mb_substr($conv->contactName, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        {{-- Infos --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $conv->contactName }}</p>
                                <span class="text-xs text-gray-400 flex-shrink-0">{{ $conv->timeAgo() }}</span>
                            </div>
                            <p class="conv-last-msg text-xs text-gray-500 truncate mt-0.5">{{ $conv->lastMessage ?? 'Pas de message' }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium {{ $conv->statusBadgeClass() }}">
                                    {{ $conv->statusLabel() }}
                                </span>
                                @if($conv->assigneeName)
                                    <span class="text-[10px] text-gray-400">{{ $conv->assigneeName }}</span>
                                @else
                                    <span class="text-[10px] text-orange-500 font-medium">Non assigne</span>
                                @endif
                                @if($conv->unreadCount > 0)
                                    <span class="conv-unread-badge ml-auto bg-primary-500 text-white text-[10px] font-bold min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center">
                                        {{ $conv->unreadCount }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-sm">Aucune conversation {{ $currentStatus }}</p>
                </div>
            @endforelse

            {{-- Sentinel pour pagination infinie --}}
            <div id="load-more-conv" class="py-4 text-center hidden">
                <svg class="w-5 h-5 animate-spin mx-auto text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            </div>
        </div>
    </div>

    {{-- ═══ ZONE CHAT (chargee en AJAX) ═══ --}}
    <div id="chat-zone" class="flex-1 flex flex-col min-w-0 h-full overflow-hidden bg-gray-50">
        {{-- Placeholder --}}
        <div id="chat-placeholder" class="flex-1 flex items-center justify-center">
            <div class="text-center text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-lg font-medium">Selectionnez une conversation</p>
                <p class="text-sm mt-1">Choisissez une conversation dans la liste pour commencer</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    let activeId = null;
    const SIDEBAR_POLL = 15000;
    let convPage = {{ $currentPage ?? 1 }};
    let loadingMore = false;
    let noMoreConvs = false;
    let autoAssignEnabled = false;

    // ═══ Auto-assignation toggle ═══
    (async function loadAutoAssignState() {
        try {
            const r = await fetch('/ajax/inboxes');
            const inboxes = await r.json();
            if (inboxes.length > 0) {
                // Store first inbox id for toggle
                window._primaryInboxId = inboxes[0].id;
                autoAssignEnabled = !!(inboxes[0].enable_auto_assignment);
                updateAutoAssignUI();
            }
        } catch(e) {}
    })();

    function updateAutoAssignUI() {
        const btn = document.getElementById('auto-assign-toggle');
        const dot = document.getElementById('auto-assign-dot');
        if (!btn || !dot) return;
        if (autoAssignEnabled) {
            btn.classList.remove('bg-gray-300');
            btn.classList.add('bg-primary-500');
            dot.style.transform = 'translateX(16px)';
        } else {
            btn.classList.add('bg-gray-300');
            btn.classList.remove('bg-primary-500');
            dot.style.transform = 'translateX(0)';
        }
    }

    window.toggleAutoAssignment = async function() {
        if (!window._primaryInboxId) return;
        autoAssignEnabled = !autoAssignEnabled;
        updateAutoAssignUI();
        try {
            await fetch(`/ajax/inboxes/${window._primaryInboxId}/auto-assignment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ auto_assignment: autoAssignEnabled })
            });
        } catch(e) {
            autoAssignEnabled = !autoAssignEnabled;
            updateAutoAssignUI();
        }
    };

    window.loadConversation = async function(id) {
        // Forcer le rechargement si on reclique sur la meme (pour rafraichir)
        const zone = document.getElementById('chat-zone');

        // Loading state
        zone.innerHTML = '<div class="flex-1 flex items-center justify-center"><div class="animate-pulse text-gray-400"><svg class="w-8 h-8 animate-spin mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><p class="text-sm">Chargement...</p></div></div>';

        // Highlight active conversation + hide unread badge
        document.querySelectorAll('.conv-item').forEach(el => {
            el.classList.remove('bg-primary-50', 'border-l-4', 'border-l-primary-500');
        });
        const active = document.querySelector(`.conv-item[data-id="${id}"]`);
        if (active) {
            active.classList.add('bg-primary-50', 'border-l-4', 'border-l-primary-500');
            // Supprimer le badge unread
            const badge = active.querySelector('.conv-unread-badge');
            if (badge) badge.remove();
            active.dataset.unread = '0';
        }

        try {
            // Charger le panel + marquer comme lu en parallele
            const [r] = await Promise.all([
                fetch(`/ajax/conversations/${id}/panel`),
                fetch(`/ajax/conversations/${id}/read`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': TOKEN },
                }),
            ]);
            if (!r.ok) throw new Error('HTTP ' + r.status);
            const html = await r.text();
            zone.innerHTML = html;
            activeId = id;

            // Execute inline scripts
            zone.querySelectorAll('script').forEach(oldScript => {
                const newScript = document.createElement('script');
                newScript.textContent = oldScript.textContent;
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });

            // Update URL
            history.pushState({ conversationId: id }, '', `/conversations/${id}`);
        } catch(e) {
            console.error('Load panel:', e);
            zone.innerHTML = '<div class="flex-1 flex items-center justify-center"><p class="text-red-500 text-sm">Erreur de chargement. Reessayez.</p></div>';
        }
    };

    // ═══ Polling sidebar: detecter nouveaux messages / conversations ═══
    if (window._sidebarPollTimer) clearInterval(window._sidebarPollTimer);
    window._sidebarPollTimer = setInterval(async () => {
        try {
            const r = await fetch('/ajax/conversations/counts');
            const counts = await r.json();
            // Mettre a jour le compteur total dans le header
            const totalEl = document.querySelector('.text-sm.text-gray-500');
            if (totalEl && counts.all_count !== undefined) {
                totalEl.textContent = counts.all_count + ' total';
            }
            // Mettre a jour les compteurs des tabs
            const tabLinks = document.querySelectorAll('.flex.gap-1 a');
            const tabCountKeys = ['open_count', 'pending_count', 'resolved_count'];
            tabLinks.forEach((link, idx) => {
                const key = tabCountKeys[idx];
                if (counts[key] !== undefined) {
                    let countSpan = link.querySelector('span');
                    if (countSpan) {
                        countSpan.textContent = '(' + counts[key] + ')';
                    }
                }
            });
        } catch(e) {}

        // Rafraichir la liste pour afficher les nouveaux messages/badges
        try {
            const status = '{{ $currentStatus }}';
            const assignee = document.getElementById('filter-assignee')?.value || 'all';
            const r = await fetch(`/ajax/conversations/list-update?status=${status}&assignee_type=${assignee}`);
            if (!r.ok) return;
            const data = await r.json();
            if (!data.conversations) return;

            data.conversations.forEach(conv => {
                const item = document.querySelector(`.conv-item[data-id="${conv.id}"]`);
                if (!item) return;
                // Ne pas modifier la conversation active (ouverte)
                if (conv.id == activeId) return;

                const oldUnread = parseInt(item.dataset.unread || '0');
                const newUnread = conv.unread_count || 0;
                const lastMsgType = conv.last_message_type; // 0=client, 1=agent, 2=activity

                // Mettre a jour le dernier message
                const lastMsgEl = item.querySelector('.conv-last-msg');
                if (lastMsgEl && conv.last_message) {
                    lastMsgEl.textContent = conv.last_message;
                }

                // Badge unread : uniquement si le dernier message vient du CLIENT (type 0)
                const badge = item.querySelector('.conv-unread-badge');
                if (lastMsgType === 0 && newUnread > 0) {
                    // Toast + son si nouveau message client
                    if ((!badge || newUnread > oldUnread)) {
                        if (window._playNotifSound) window._playNotifSound();
                        if (window._showMsgToast) {
                            window._showMsgToast(conv.id, conv.contact_name, conv.contact_thumbnail, conv.last_message);
                        }
                    }
                    // Message client non lu → afficher/maj le badge
                    item.dataset.unread = newUnread;
                    if (!badge) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'conv-unread-badge ml-auto bg-primary-500 text-white text-[10px] font-bold min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center';
                        newBadge.textContent = newUnread;
                        const statusLine = item.querySelector('.flex.items-center.gap-2.mt-1');
                        if (statusLine) statusLine.appendChild(newBadge);
                    } else {
                        badge.textContent = newUnread;
                    }
                } else if (lastMsgType === 1 && badge) {
                    // Dernier message = agent → supprimer le badge (l'agent a repondu)
                    badge.remove();
                    item.dataset.unread = '0';
                }
            });
        } catch(e) {}
    }, SIDEBAR_POLL);

    // ═══ Ensure conversation appears in sidebar (after reopen, send, status change) ═══
    window._ensureConvInSidebar = async function(convId, options = {}) {
        const list = document.getElementById('conversation-list');
        if (!list) return;
        let item = list.querySelector(`.conv-item[data-id="${convId}"]`);

        // If the item exists, move it to the top
        if (item) {
            const loadMore = document.getElementById('load-more-conv');
            list.insertBefore(item, list.firstChild);
            // Update last message if provided
            if (options.lastMessage) {
                const msgEl = item.querySelector('.conv-last-msg');
                if (msgEl) msgEl.textContent = options.lastMessage;
            }
            // Update status badge if provided
            if (options.status) {
                const _sl = {'open':'Ouvert','pending':'En attente','resolved':'Résolu','snoozed':'Reporté'};
                const _sc = {'open':'bg-blue-50 text-blue-700','pending':'bg-amber-50 text-amber-700','resolved':'bg-green-50 text-green-700','snoozed':'bg-gray-100 text-gray-600'};
                const badge = item.querySelector('.inline-block.px-1\\.5');
                if (badge) {
                    badge.textContent = _sl[options.status] || options.status;
                    badge.className = 'inline-block px-1.5 py-0.5 rounded text-[10px] font-medium ' + (_sc[options.status] || 'bg-gray-100 text-gray-600');
                }
            }
            return;
        }

        // Item not in sidebar — fetch conversation data from API and create it
        try {
            const r = await fetch(`/ajax/conversations/${convId}/poll?last_message_id=0`);
            const pollData = await r.json();
            const lastMsg = (pollData.messages && pollData.messages.length > 0)
                ? pollData.messages[pollData.messages.length - 1]
                : null;

            // Also get conversation details from list-update
            const r2 = await fetch(`/ajax/conversations/list-update?status=open&assignee_type=all`);
            const listData = await r2.json();
            const conv = (listData.conversations || []).find(c => c.id == convId);

            const contactName = conv?.contact_name || options.contactName || 'Contact';
            const contactThumb = conv?.contact_thumbnail || options.contactThumbnail || null;
            const lastMessage = options.lastMessage || conv?.last_message || lastMsg?.content || 'Pas de message';
            const status = options.status || conv?.status || 'open';
            const initial = contactName.charAt(0).toUpperCase();

            const avatarHtml = contactThumb
                ? `<img src="${contactThumb}" class="w-10 h-10 rounded-full" alt="">`
                : `<div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-semibold text-sm">${initial}</div>`;

            const statusClasses = {
                'open': 'bg-blue-50 text-blue-700',
                'pending': 'bg-amber-50 text-amber-700',
                'resolved': 'bg-green-50 text-green-700',
                'snoozed': 'bg-gray-100 text-gray-600',
            };
            const statusLabels = {
                'open': 'Ouvert',
                'pending': 'En attente',
                'resolved': 'Résolu',
                'snoozed': 'Reporté',
            };
            const statusClass = statusClasses[status] || 'bg-gray-100 text-gray-600';
            const statusText = statusLabels[status] || status;

            const div = document.createElement('div');
            div.className = 'conv-item cursor-pointer px-4 py-3 border-b hover:bg-gray-50 transition';
            div.dataset.id = convId;
            div.dataset.unread = '0';
            div.onclick = () => loadConversation(convId);
            div.innerHTML = `<div class="flex items-start gap-3">
                <div class="flex-shrink-0">${avatarHtml}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-900 truncate">${contactName}</p>
                        <span class="text-xs text-gray-400 flex-shrink-0">A l'instant</span>
                    </div>
                    <p class="conv-last-msg text-xs text-gray-500 truncate mt-0.5">${lastMessage}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium ${statusClass}">${statusText}</span>
                    </div>
                </div>
            </div>`;

            list.insertBefore(div, list.firstChild);
        } catch(e) {
            console.error('_ensureConvInSidebar:', e);
        }
    };

    // ═══ Remove conversation from sidebar ═══
    window._removeConvFromSidebar = function(convId) {
        const item = document.querySelector(`.conv-item[data-id="${convId}"]`);
        if (item) item.remove();
    };

    // ═══ Auto-open conversation if selectedConversationId is set (page reload on /conversations/{id}) ═══
    @if($selectedConversationId)
        loadConversation({{ $selectedConversationId }});
    @endif

    // Handle browser back/forward
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.conversationId) {
            loadConversation(e.state.conversationId);
        } else {
            activeId = null;
            const zone = document.getElementById('chat-zone');
            zone.innerHTML = '<div class="flex-1 flex items-center justify-center"><div class="text-center text-gray-400"><svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg><p class="text-lg font-medium">Selectionnez une conversation</p></div></div>';
            document.querySelectorAll('.conv-item').forEach(el => {
                el.classList.remove('bg-primary-50', 'border-l-4', 'border-l-primary-500');
            });
        }
    });

    // ═══ Load labels into filter dropdown ═══
    (async function() {
        try {
            const r = await fetch('/ajax/labels');
            const labels = await r.json();
            const sel = document.getElementById('filter-label');
            const currentLabel = '{{ $currentLabel ?? '' }}';
            labels.forEach(l => {
                const title = l.title || l;
                const color = l.color || null;
                const opt = document.createElement('option');
                opt.value = title;
                opt.textContent = title;
                if (title === currentLabel) opt.selected = true;
                sel.appendChild(opt);
            });
        } catch(e) {}
    })();

    // ═══ Filters ═══
    window.applyFilters = function() {
        const assignee = document.getElementById('filter-assignee').value;
        const label = document.getElementById('filter-label').value;
        const params = new URLSearchParams(window.location.search);
        params.set('assignee_type', assignee);
        if (label) {
            params.set('label', label);
        } else {
            params.delete('label');
        }
        window.location.href = '{{ route("conversations.index") }}?' + params.toString();
    };

    // ═══ Advanced filters ═══
    let advFiltersOpen = false;
    window.toggleAdvancedFilters = function() {
        const panel = document.getElementById('adv-filter-panel');
        const btn = document.getElementById('adv-filter-btn');
        advFiltersOpen = !advFiltersOpen;
        panel.classList.toggle('hidden', !advFiltersOpen);
        btn.classList.toggle('bg-primary-100', advFiltersOpen);
        btn.classList.toggle('text-primary-700', advFiltersOpen);
    };

    window.resetAdvancedFilters = function() {
        document.getElementById('adv-status').value = '';
        document.getElementById('adv-agent').value = '';
        document.getElementById('adv-team').value = '';
        document.getElementById('adv-label').value = '';
        document.getElementById('adv-since').value = '';
        document.getElementById('adv-until').value = '';
        // Reload without filters
        window.location.href = '{{ route("conversations.index") }}';
    };

    window.applyAdvancedFilters = async function() {
        const filters = {};
        const status = document.getElementById('adv-status').value;
        const agent = document.getElementById('adv-agent').value;
        const team = document.getElementById('adv-team').value;
        const label = document.getElementById('adv-label').value;
        const since = document.getElementById('adv-since').value;
        const until = document.getElementById('adv-until').value;

        if (status) filters.status = status;
        if (agent) filters.assignee_id = agent;
        if (team) filters.team_id = team;
        if (label) filters.label = label;
        if (since) filters.created_since = since;
        if (until) filters.created_until = until;

        if (Object.keys(filters).length === 0) return;

        const list = document.getElementById('conversation-list');
        list.innerHTML = '<div class="p-8 text-center text-gray-400"><svg class="w-6 h-6 animate-spin mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg><p class="text-xs">Filtrage...</p></div>';

        try {
            const r = await fetch('{{ route("ajax.filter") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ filters })
            });
            const data = await r.json();
            const convs = data.conversations || [];

            if (convs.length === 0) {
                list.innerHTML = '<div class="p-8 text-center text-gray-400"><p class="text-sm">Aucun resultat</p></div>';
                return;
            }

            const _statusClasses = {
                'open': 'bg-blue-50 text-blue-700',
                'pending': 'bg-amber-50 text-amber-700',
                'resolved': 'bg-green-50 text-green-700',
                'snoozed': 'bg-gray-100 text-gray-600',
            };
            const _statusLabels = {
                'open': 'Ouvert',
                'pending': 'En attente',
                'resolved': 'Résolu',
                'snoozed': 'Reporté',
            };

            list.innerHTML = '';
            convs.forEach(conv => {
                const initial = (conv.contactName || '?').charAt(0).toUpperCase();
                const avatarHtml = conv.contactThumbnail
                    ? `<img src="${conv.contactThumbnail}" class="w-10 h-10 rounded-full" alt="">`
                    : `<div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-semibold text-sm">${initial}</div>`;
                const unreadHtml = conv.unreadCount > 0
                    ? `<span class="conv-unread-badge ml-auto bg-primary-500 text-white text-[10px] font-bold min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center">${conv.unreadCount}</span>`
                    : '';
                const assigneeHtml = conv.assigneeName
                    ? `<span class="text-[10px] text-gray-400">${conv.assigneeName}</span>`
                    : '<span class="text-[10px] text-orange-500 font-medium">Non assigne</span>';
                const badgeClass = _statusClasses[conv.status] || 'bg-gray-100 text-gray-600';
                const badgeLabel = _statusLabels[conv.status] || conv.status;

                const div = document.createElement('div');
                div.className = 'conv-item cursor-pointer px-4 py-3 border-b hover:bg-gray-50 transition';
                div.dataset.id = conv.id;
                div.dataset.unread = conv.unreadCount || 0;
                div.onclick = () => loadConversation(conv.id);
                div.innerHTML = `<div class="flex items-start gap-3">
                    <div class="flex-shrink-0">${avatarHtml}</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-900 truncate">${conv.contactName || 'Contact'}</p>
                            <span class="text-xs text-gray-400 flex-shrink-0">${conv.timeAgo || ''}</span>
                        </div>
                        <p class="conv-last-msg text-xs text-gray-500 truncate mt-0.5">${conv.lastMessage || 'Pas de message'}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium ${badgeClass}">${badgeLabel}</span>
                            ${assigneeHtml}
                            ${unreadHtml}
                        </div>
                    </div>
                </div>`;
                list.appendChild(div);
            });
        } catch(e) {
            list.innerHTML = '<div class="p-8 text-center text-red-400"><p class="text-sm">Erreur de filtrage</p></div>';
        }
    };

    // Load teams and labels into advanced filter dropdowns
    (async function() {
        try {
            const [teamsR, labelsR] = await Promise.all([fetch('/ajax/teams'), fetch('/ajax/labels')]);
            const teams = await teamsR.json();
            const labels = await labelsR.json();
            const teamSel = document.getElementById('adv-team');
            const labelSel = document.getElementById('adv-label');
            teams.forEach(t => {
                const o = document.createElement('option');
                o.value = t.id;
                o.textContent = t.name;
                teamSel.appendChild(o);
            });
            labels.forEach(l => {
                const title = l.title || l;
                const o = document.createElement('option');
                o.value = title;
                o.textContent = title;
                labelSel.appendChild(o);
            });
        } catch(e) {}
    })();

    // ═══ Pagination infinie conversations ═══
    const convList = document.getElementById('conversation-list');
    const loadMoreEl = document.getElementById('load-more-conv');

    if (convList && loadMoreEl) {
        const observer = new IntersectionObserver(async (entries) => {
            if (!entries[0].isIntersecting || loadingMore || noMoreConvs) return;
            loadingMore = true;
            loadMoreEl.classList.remove('hidden');
            convPage++;
            const params = new URLSearchParams(window.location.search);
            params.set('page', convPage);
            try {
                const r = await fetch('{{ route("conversations.index") }}?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const html = await r.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newItems = doc.querySelectorAll('.conv-item');
                if (newItems.length === 0) {
                    noMoreConvs = true;
                    loadMoreEl.innerHTML = '<span class="text-[10px] text-gray-300">Toutes les conversations chargees</span>';
                    return;
                }
                newItems.forEach(item => {
                    const id = item.dataset.id;
                    // Ne pas dupliquer
                    if (convList.querySelector(`.conv-item[data-id="${id}"]`)) return;
                    const clone = item.cloneNode(true);
                    clone.addEventListener('click', () => loadConversation(parseInt(id)));
                    convList.insertBefore(clone, loadMoreEl);
                });
            } catch(e) { console.error('LoadMore:', e); }
            finally {
                loadingMore = false;
                if (!noMoreConvs) loadMoreEl.classList.add('hidden');
            }
        }, { root: convList, threshold: 0.1 });
        observer.observe(loadMoreEl);
        // Rendre visible pour l'observer
        loadMoreEl.classList.remove('hidden');
        loadMoreEl.classList.add('invisible');
        loadMoreEl.style.height = '1px';
        // Afficher correctement quand actif
        loadMoreEl.classList.remove('invisible');
        loadMoreEl.style.height = '';
        loadMoreEl.classList.add('hidden');
        // Rendre visible seulement comme sentinel
        setTimeout(() => {
            loadMoreEl.style.height = '1px';
            loadMoreEl.style.overflow = 'hidden';
            loadMoreEl.classList.remove('hidden');
            loadMoreEl.style.opacity = '0';
        }, 500);
    }
})();
</script>
@endpush
