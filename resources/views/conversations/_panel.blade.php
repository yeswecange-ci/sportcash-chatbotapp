{{-- Panneau chat (chargé en AJAX dans la zone droite) --}}
<div class="flex h-full" id="chat-panel" data-conversation-id="{{ $conversation->id }}">

    {{-- ═══ Zone Chat principale ═══ --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Header --}}
        <div class="h-14 px-5 bg-white border-b border-gray-200 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                @if($contact && ($contact['thumbnail'] ?? null))
                    <img src="{{ $contact['thumbnail'] }}" class="w-8 h-8 rounded-full flex-shrink-0" alt="">
                @else
                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-semibold flex-shrink-0">
                        {{ mb_strtoupper(mb_substr($conversation->contactName, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-gray-900 truncate">{{ $conversation->contactName }}</h2>
                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                        <span class="truncate">{{ $conversation->contactPhone ?? '' }}</span>
                        <span class="inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $conversation->statusBadgeClass() }}">{{ $conversation->statusLabel() }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                {{-- Assign agent --}}
                <select id="assign-select"
                        class="text-[11px] border-gray-200 rounded-lg px-2 py-1.5 bg-gray-50 focus:ring-2 focus:ring-primary-500">
                    <option value="">Non assigne</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent['id'] }}" {{ ($assignee['id'] ?? null) == $agent['id'] ? 'selected' : '' }}>
                            {{ $agent['name'] }}
                        </option>
                    @endforeach
                </select>

                {{-- Assign team --}}
                <select id="team-select"
                        class="text-[11px] border-gray-200 rounded-lg px-2 py-1.5 bg-gray-50 focus:ring-2 focus:ring-primary-500">
                    <option value="">Aucune equipe</option>
                </select>

                @if($conversation->status === 'resolved')
                    <button onclick="toggleStatus('reopen')"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reouvrir
                    </button>
                @else
                    @if($conversation->status !== 'pending')
                    <button onclick="toggleStatus('pending')"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        En attente
                    </button>
                    @endif
                    <button onclick="toggleStatus('resolve')"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-medium bg-green-50 text-green-700 border border-green-200 rounded-lg hover:bg-green-100 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Resoudre
                    </button>
                @endif

                {{-- Toggle sidebar contact --}}
                <button onclick="toggleContactSidebar()"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                        title="Infos contact">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </button>

                {{-- Supprimer conversation --}}
                <button onclick="deleteConversation()"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition"
                        title="Supprimer la conversation">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>

                {{-- Ouvrir plein ecran --}}
                <a href="{{ route('conversations.show', $conversation->id) }}"
                   class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                   title="Ouvrir en plein ecran">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>

        {{-- Labels bar --}}
        <div id="labels-bar" class="px-5 py-2 bg-white border-b border-gray-100 flex items-center gap-2 flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            <div id="labels-container" class="flex items-center gap-1.5 flex-wrap flex-1">
                @foreach($conversation->labels as $label)
                    <span class="conv-label inline-flex items-center gap-1 px-2 py-0.5 bg-primary-50 text-primary-700 rounded-full text-[10px] font-medium" data-label="{{ $label }}">
                        {{ $label }}
                        <button onclick="removeLabel('{{ $label }}')" class="hover:text-red-500 transition">&times;</button>
                    </span>
                @endforeach
            </div>
            <div class="relative flex-shrink-0">
                <button onclick="toggleLabelDropdown()" class="w-6 h-6 flex items-center justify-center rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition" title="Ajouter un label">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <div id="label-dropdown" class="hidden absolute right-0 top-8 z-20 bg-white border border-gray-200 rounded-xl shadow-lg py-2 w-56 max-h-64 overflow-y-auto">
                    <div class="px-3 py-1.5">
                        <input id="label-search" type="text" placeholder="Chercher ou creer un label..."
                               class="w-full px-2 py-1 text-xs border border-gray-200 rounded-md focus:ring-1 focus:ring-primary-500"
                               oninput="filterLabels()" onkeydown="handleLabelKeydown(event)">
                    </div>
                    <div id="label-options" class="mt-1"></div>
                    <div id="label-create-option" class="hidden px-3 py-2 border-t border-gray-100 mt-1">
                        <button id="label-create-btn" onclick="createAndAddLabel()" class="w-full flex items-center gap-2 text-xs text-primary-600 hover:text-primary-700 font-medium py-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Creer "<span id="label-create-name"></span>"</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Messages --}}
        <div id="messages-container" class="flex-1 overflow-y-auto px-5 py-4 space-y-3 bg-gray-50 scrollbar-thin">
            <div id="load-more-sentinel" class="text-center py-2 hidden">
                <span class="text-[10px] text-gray-400">Chargement des anciens messages...</span>
            </div>
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
                    <div class="group/msg {{ $isOutgoing ? 'message-outgoing' : 'message-incoming' }}" data-msg-id="{{ $msg['id'] ?? '' }}">
                        <div class="relative max-w-[80%] {{ $isPrivate ? 'bg-amber-50 border border-amber-200' : ($isOutgoing ? 'bg-primary-600 text-white' : 'bg-white border border-gray-200') }} rounded-2xl {{ $isOutgoing ? 'rounded-br-md' : 'rounded-bl-md' }} px-4 py-2.5 shadow-sm">
                            @if($isOutgoing && isset($msg['id']))
                            <button onclick="deleteMsg({{ $msg['id'] }})" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full items-center justify-center text-[10px] shadow hidden group-hover/msg:flex hover:bg-red-600 transition" title="Supprimer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endif
                            @if($isPrivate)
                                <p class="text-[10px] font-semibold text-amber-600 mb-0.5">Note privee</p>
                            @endif
                            @if(($msg['content'] ?? '') !== '')
                                <p class="text-[13px] leading-relaxed whitespace-pre-wrap">{{ $msg['content'] }}</p>
                            @endif

                            {{-- Attachments --}}
                            @if(!empty($msg['attachments']))
                                <div class="mt-1.5 space-y-1.5">
                                    @foreach($msg['attachments'] as $att)
                                        @php $fileType = $att['file_type'] ?? 'file'; @endphp

                                        @if($fileType === 'image')
                                            <a href="{{ $att['data_url'] }}" target="_blank" class="block">
                                                <img src="{{ $att['data_url'] }}" alt="{{ $att['file_name'] ?? 'image' }}"
                                                     class="max-w-full rounded-lg max-h-64 object-cover cursor-pointer hover:opacity-90 transition" loading="lazy">
                                            </a>
                                        @elseif($fileType === 'audio')
                                            <div class="audio-player flex items-center gap-2 py-1 min-w-[220px] max-w-[280px]" data-src="{{ $att['data_url'] }}">
                                                <button type="button" class="audio-play-btn w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $isOutgoing ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-primary-100 hover:bg-primary-200 text-primary-600' }} transition">
                                                    <svg class="audio-icon-play w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                    <svg class="audio-icon-pause w-4 h-4 hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                                </button>
                                                <div class="flex-1 min-w-0">
                                                    <div class="audio-progress-bar relative h-1.5 rounded-full {{ $isOutgoing ? 'bg-white/20' : 'bg-gray-200' }} cursor-pointer">
                                                        <div class="audio-progress absolute left-0 top-0 h-full rounded-full {{ $isOutgoing ? 'bg-white' : 'bg-primary-500' }} transition-all" style="width:0%"></div>
                                                    </div>
                                                    <div class="flex justify-between mt-1">
                                                        <span class="audio-current text-[10px] {{ $isOutgoing ? 'text-primary-200' : 'text-gray-400' }}">0:00</span>
                                                        <span class="audio-duration text-[10px] {{ $isOutgoing ? 'text-primary-200' : 'text-gray-400' }}">--:--</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($fileType === 'video')
                                            <video controls preload="metadata" class="max-w-full rounded-lg max-h-64">
                                                <source src="{{ $att['data_url'] }}">
                                            </video>
                                        @else
                                            <a href="{{ $att['data_url'] }}" target="_blank"
                                               class="flex items-center gap-2 px-3 py-2 rounded-lg {{ $isOutgoing ? 'bg-primary-700/30 hover:bg-primary-700/50' : 'bg-gray-100 hover:bg-gray-200' }} transition">
                                                <svg class="w-5 h-5 flex-shrink-0 {{ $isOutgoing ? 'text-primary-200' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <div class="min-w-0">
                                                    <p class="text-xs font-medium truncate">{{ $att['file_name'] ?? 'Fichier' }}</p>
                                                    @if(isset($att['file_size']))
                                                        <p class="text-[10px] {{ $isOutgoing ? 'text-primary-300' : 'text-gray-400' }}">{{ number_format($att['file_size'] / 1024, 0, ',', ' ') }} Ko</p>
                                                    @endif
                                                </div>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <p class="text-[10px] mt-1 {{ $isPrivate ? 'text-amber-400' : ($isOutgoing ? 'text-primary-200' : 'text-gray-400') }}">
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

        {{-- Typing indicator --}}
        <div id="typing-indicator" class="hidden px-5 py-1.5 bg-gray-50 border-t border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="flex gap-0.5">
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                </div>
                <span class="text-[11px] text-gray-400 italic">en train d'ecrire...</span>
            </div>
        </div>

        {{-- Bannière fenêtre WhatsApp expirée --}}
        @if($windowExpired ?? false)
        <div id="window-expired-banner" class="px-4 py-2.5 bg-amber-50 border-t border-amber-200 flex items-center gap-2 flex-shrink-0">
            <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <p class="text-xs text-amber-700">La fenetre WhatsApp de 24h est expiree. Utilisez un <strong>template</strong> pour relancer la conversation.</p>
        </div>
        @endif

        {{-- Template Picker (toujours disponible si Twilio configuré, affiché par défaut si fenêtre expirée) --}}
        @if($twilioConfigured ?? false)
        <div id="template-picker" class="bg-white border-t border-gray-200 px-4 py-3 flex-shrink-0 space-y-3 {{ ($windowExpired ?? false) ? '' : 'hidden' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 flex-1">
                    <svg class="w-4 h-4 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <select id="template-select" class="flex-1 text-xs border-gray-200 rounded-lg px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-primary-500">
                        <option value="">-- Choisir un template --</option>
                    </select>
                </div>
                <button onclick="toggleTemplatePicker()" class="ml-2 w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition" title="Revenir au message libre">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Variables dynamiques --}}
            <div id="template-variables" class="hidden space-y-2"></div>

            {{-- Aperçu --}}
            <div id="template-preview" class="hidden p-3 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Apercu du message</p>
                <p id="template-preview-text" class="text-xs text-gray-700 whitespace-pre-wrap"></p>
            </div>

            {{-- Bouton envoyer --}}
            <button id="send-template-btn" onclick="sendTemplateMsg()" disabled
                    class="w-full py-2 text-xs font-medium bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Envoyer le template
            </button>
        </div>
        @endif

        {{-- Zone de saisie avec autocomplétion --}}
        <div class="bg-white border-t border-gray-200 px-4 py-3 flex-shrink-0 relative {{ ($windowExpired ?? false) ? 'hidden' : '' }}" id="composer-zone">
            {{-- Canned Responses dropdown --}}
            <div id="canned-dropdown" class="hidden absolute bottom-full left-4 right-4 mb-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-y-auto z-30">
                <div class="p-2 text-[10px] font-medium text-gray-400 uppercase tracking-wider">Reponses rapides</div>
                <div id="canned-list"></div>
            </div>

            {{-- File preview zone --}}
            <div id="file-preview" class="hidden mb-2 flex flex-wrap gap-2"></div>

            <form id="message-form" class="flex items-end gap-2">
                <input type="file" id="file-input" multiple class="hidden"
                       accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo,audio/mpeg,audio/ogg,audio/wav,audio/x-m4a,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                <div class="flex-1">
                    <textarea id="message-input"
                              rows="1"
                              placeholder="Ecrivez votre message... (tapez / pour les reponses rapides)"
                              class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm resize-none bg-gray-50 placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 focus:bg-white transition"></textarea>
                </div>
                <div class="flex items-center gap-1.5 pb-0.5">
                    <button type="button" onclick="document.getElementById('file-input').click()"
                            title="Joindre un fichier"
                            class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 border border-gray-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    </button>
                    <button type="button" onclick="sendPrivateNote()"
                            title="Note privee"
                            class="w-9 h-9 flex items-center justify-center rounded-lg text-amber-500 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </button>
                    @if($twilioConfigured ?? false)
                    <button type="button" onclick="toggleTemplatePicker()"
                            title="Envoyer un template WhatsApp"
                            class="w-9 h-9 flex items-center justify-center rounded-lg text-green-600 bg-green-50 border border-green-200 hover:bg-green-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </button>
                    @endif
                    <button type="submit" id="send-btn"
                            class="w-9 h-9 flex items-center justify-center rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition shadow-sm">
                        <svg id="send-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <svg id="send-loader" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ Sidebar Contact (toggle) ═══ --}}
    <div id="contact-sidebar" class="w-72 bg-white border-l border-gray-200 flex-col flex-shrink-0 overflow-y-auto hidden">

        {{-- Contact header --}}
        <div class="p-5 border-b border-gray-100 text-center relative">
            <button onclick="toggleContactSidebar()"
                    class="absolute top-3 right-3 w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                    title="Fermer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            @if($contact && ($contact['thumbnail'] ?? null))
                <img src="{{ $contact['thumbnail'] }}" class="w-14 h-14 rounded-full mx-auto mb-2" alt="">
            @else
                <div class="w-14 h-14 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-lg font-bold mx-auto mb-2">
                    {{ mb_strtoupper(mb_substr($conversation->contactName, 0, 1)) }}
                </div>
            @endif
            <h3 class="font-semibold text-gray-900 text-sm">{{ $contact['name'] ?? $conversation->contactName }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $contact['phone_number'] ?? $conversation->contactPhone ?? '' }}</p>
            @if($contact['email'] ?? null)
                <p class="text-xs text-gray-500">{{ $contact['email'] }}</p>
            @endif
        </div>

        {{-- Tabs: Info / Conversations / Notes --}}
        <div class="flex border-b border-gray-100">
            <button onclick="showContactTab('info')" data-tab="info" class="contact-tab flex-1 py-2.5 text-[11px] font-medium text-center border-b-2 border-primary-500 text-primary-700">Infos</button>
            <button onclick="showContactTab('convos')" data-tab="convos" class="contact-tab flex-1 py-2.5 text-[11px] font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700">Historique</button>
            <button onclick="showContactTab('notes')" data-tab="notes" class="contact-tab flex-1 py-2.5 text-[11px] font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700">Notes</button>
        </div>

        {{-- Tab: Info --}}
        <div id="tab-info" class="contact-tab-content p-4 space-y-3 text-sm">
            <div>
                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Conversation</p>
                <p class="text-gray-700 mt-0.5 font-mono text-xs">#{{ $conversation->id }}</p>
            </div>
            <div>
                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Creee le</p>
                <p class="text-gray-700 mt-0.5 text-xs">{{ $conversation->createdAt }}</p>
            </div>
            @if($conversation->assigneeName)
            <div>
                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Assignee a</p>
                <p class="text-gray-700 mt-0.5 text-xs">{{ $conversation->assigneeName }}</p>
            </div>
            @endif
            @if(count($conversation->labels) > 0)
            <div>
                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Labels</p>
                <div class="flex flex-wrap gap-1">
                    @foreach($conversation->labels as $label)
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px]">{{ $label }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Tab: Conversations history --}}
        <div id="tab-convos" class="contact-tab-content hidden p-4">
            <div id="contact-convos-list" class="space-y-2">
                <div class="text-center py-4"><span class="text-xs text-gray-400">Chargement...</span></div>
            </div>
        </div>

        {{-- Tab: Notes --}}
        <div id="tab-notes" class="contact-tab-content hidden p-4">
            <form id="note-form" class="mb-3">
                <textarea id="note-input" rows="2" placeholder="Ajouter une note..."
                          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs resize-none focus:ring-2 focus:ring-primary-500"></textarea>
                <button type="submit" class="mt-1 w-full py-1.5 text-[11px] font-medium bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">Ajouter</button>
            </form>
            <div id="contact-notes-list" class="space-y-2">
                <div class="text-center py-4"><span class="text-xs text-gray-400">Chargement...</span></div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const CID = {{ $conversation->id }};
    const CONTACT_ID = {{ $contact['id'] ?? 0 }};
    const POLL = {{ config('chatwoot.polling_interval', 4000) }};
    const TOKEN = document.querySelector('meta[name="csrf-token"]').content;
    const container = document.getElementById('messages-container');
    const form = document.getElementById('message-form');
    const input = document.getElementById('message-input');
    const fileInput = document.getElementById('file-input');
    const filePreview = document.getElementById('file-preview');
    const assignSel = document.getElementById('assign-select');
    const teamSel = document.getElementById('team-select');
    let selectedFiles = [];

    let lastId = {{ collect($messages)->max('id') ?? 0 }};
    const displayedIds = new Set(@json(collect($messages)->pluck('id')->values()));

    function showPanelToast(msg, isError = false) {
        let t = document.getElementById('panel-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'panel-toast';
            t.className = 'fixed bottom-6 right-6 z-50 px-4 py-2 rounded-lg shadow-lg text-sm font-medium text-white transition-all duration-300';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.className = t.className.replace(/bg-\w+-\d+/g, '');
        t.classList.add(isError ? 'bg-red-600' : 'bg-green-600');
        t.classList.remove('hidden');
        clearTimeout(t._hideTimer);
        t._hideTimer = setTimeout(() => t.classList.add('hidden'), isError ? 4000 : 2500);
    }
    let cannedResponses = [];
    let allLabels = [];
    let currentLabels = @json($conversation->labels);
    let oldestMsgId = {{ collect($messages)->min('id') ?? 0 }};
    let loadingOlder = false;
    let noMoreOlder = false;
    let typingTimer = null;
    let isTypingSent = false;
    const WINDOW_EXPIRED = {{ ($windowExpired ?? false) ? 'true' : 'false' }};
    const TWILIO_CONFIGURED = {{ ($twilioConfigured ?? false) ? 'true' : 'false' }};
    let twilioTemplates = [];
    let templatesLoaded = false;

    // ═══ Toggle Template Picker ↔ Composer ═══
    window.toggleTemplatePicker = function() {
        const picker = document.getElementById('template-picker');
        const composer = document.getElementById('composer-zone');
        if (!picker) return;

        if (picker.classList.contains('hidden')) {
            picker.classList.remove('hidden');
            if (composer) composer.classList.add('hidden');
            if (!templatesLoaded) loadTwilioTemplates();
        } else {
            picker.classList.add('hidden');
            if (composer) composer.classList.remove('hidden');
        }
    };

    // ═══ Template Picker (WhatsApp) ═══
    async function loadTwilioTemplates() {
        if (templatesLoaded) return;
        templatesLoaded = true;
        try {
            const r = await fetch('/ajax/twilio/templates');
            if (!r.ok) throw new Error('Erreur chargement templates');
            twilioTemplates = await r.json();
            const sel = document.getElementById('template-select');
            twilioTemplates.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.sid;
                opt.textContent = t.friendly_name + (t.body ? ' — ' + t.body.substring(0, 50) + (t.body.length > 50 ? '...' : '') : '');
                opt.dataset.body = t.body || '';
                opt.dataset.vars = JSON.stringify(t.variables || []);
                opt.dataset.name = t.friendly_name || '';
                sel.appendChild(opt);
            });
        } catch(e) {
            console.error('Load templates:', e);
            const sel = document.getElementById('template-select');
            if (sel) sel.innerHTML = '<option value="">Erreur de chargement des templates</option>';
        }
    }

    // Listener sur le select de template
    document.getElementById('template-select')?.addEventListener('change', function() {
        const opt = this.selectedOptions[0];
        const varsDiv = document.getElementById('template-variables');
        const previewDiv = document.getElementById('template-preview');
        const sendBtn = document.getElementById('send-template-btn');

        if (!this.value) {
            varsDiv.classList.add('hidden');
            previewDiv.classList.add('hidden');
            sendBtn.disabled = true;
            return;
        }

        const body = opt.dataset.body || '';
        const vars = JSON.parse(opt.dataset.vars || '[]');

        // Afficher les inputs de variables
        if (vars.length > 0) {
            varsDiv.classList.remove('hidden');
            varsDiv.innerHTML = vars.map(v =>
                `<div class="flex items-center gap-2">
                    <label class="text-[11px] font-medium text-gray-500 w-16 flex-shrink-0">Variable ${esc(v)}</label>
                    <input type="text" class="template-var-input flex-1 px-3 py-1.5 text-xs border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-primary-500" data-var="${esc(v)}" placeholder="Valeur pour @{{${esc(v)}@}}" oninput="updateTemplatePreview()">
                </div>`
            ).join('');
        } else {
            varsDiv.classList.add('hidden');
            varsDiv.innerHTML = '';
        }

        // Afficher l'aperçu
        previewDiv.classList.remove('hidden');
        document.getElementById('template-preview-text').textContent = body;
        sendBtn.disabled = false;
        updateTemplatePreview();
    });

    // Charger les templates au démarrage si le picker est déjà visible (fenêtre expirée)
    if (TWILIO_CONFIGURED && WINDOW_EXPIRED) {
        loadTwilioTemplates();
    }

    window.updateTemplatePreview = function() {
        const sel = document.getElementById('template-select');
        if (!sel || !sel.value) return;
        const opt = sel.selectedOptions[0];
        let body = opt.dataset.body || '';

        document.querySelectorAll('.template-var-input').forEach(inp => {
            const varNum = inp.dataset.var;
            const val = inp.value || `@{{${varNum}@}}`;
            body = body.replace(new RegExp(`\\{\\{${varNum}\\}\\}`, 'g'), val);
        });

        document.getElementById('template-preview-text').textContent = body;
    };

    window.sendTemplateMsg = async function() {
        const sel = document.getElementById('template-select');
        if (!sel || !sel.value) return;

        const opt = sel.selectedOptions[0];
        const contentSid = sel.value;
        const templateName = opt.dataset.name || 'Template';
        const vars = JSON.parse(opt.dataset.vars || '[]');
        const btn = document.getElementById('send-template-btn');

        // Collecter les variables
        const variables = {};
        document.querySelectorAll('.template-var-input').forEach(inp => {
            variables[inp.dataset.var] = inp.value || '';
        });

        // Construire l'aperçu final
        let bodyPreview = opt.dataset.body || '';
        Object.entries(variables).forEach(([k, v]) => {
            bodyPreview = bodyPreview.replace(new RegExp(`\\{\\{${k}\\}\\}`, 'g'), v);
        });

        btn.disabled = true;
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Envoi en cours...';

        try {
            const r = await fetch(`/ajax/conversations/${CID}/template-message`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({
                    content_sid: contentSid,
                    variables: variables,
                    template_name: templateName,
                    body_preview: bodyPreview,
                }),
            });

            if (!r.ok) {
                const err = await r.json().catch(() => null);
                alert('Erreur: ' + (err?.error || 'Impossible d\'envoyer le template'));
                return;
            }

            const m = await r.json();
            appendMsg(m);
            lastId = Math.max(lastId, m.id);
            scroll();
            showPanelToast('Template envoye avec succes');

            // Reset
            sel.value = '';
            document.getElementById('template-variables').classList.add('hidden');
            document.getElementById('template-variables').innerHTML = '';
            document.getElementById('template-preview').classList.add('hidden');

            if (window._ensureConvInSidebar) {
                window._ensureConvInSidebar(CID, {
                    lastMessage: '[Template] ' + bodyPreview.substring(0, 50),
                    contactName: '{{ addslashes($conversation->contactName) }}',
                    contactThumbnail: '{{ $contact["thumbnail"] ?? "" }}',
                });
            }
        } catch(e) {
            console.error('Send template:', e);
            alert('Erreur d\'envoi du template');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Envoyer le template';
        }
    };

    // ═══ Stop previous polling ═══
    if (window._chatPollTimer) clearInterval(window._chatPollTimer);

    // ═══ Polling (+ typing detection from client) ═══
    window._chatPollTimer = setInterval(async () => {
        if (!document.getElementById('chat-panel') || document.getElementById('chat-panel').dataset.conversationId != CID) {
            clearInterval(window._chatPollTimer);
            return;
        }
        try {
            const r = await fetch(`/ajax/conversations/${CID}/poll?last_message_id=${lastId}`);
            const d = await r.json();
            if (d.count > 0) {
                let hasNewClientMsg = false;
                d.messages.forEach(m => {
                    appendMsg(m);
                    lastId = Math.max(lastId, m.id);
                    if (m.message_type === 0) hasNewClientMsg = true;
                });
                // Son pour nouveau message client dans le chat actif
                if (hasNewClientMsg && window._playNotifSound) window._playNotifSound();
                // Réouverture dynamique : nouveau message client → fenêtre redevient valide
                if (hasNewClientMsg && WINDOW_EXPIRED) {
                    const banner = document.getElementById('window-expired-banner');
                    const picker = document.getElementById('template-picker');
                    const composer = document.getElementById('composer-zone');
                    if (banner) banner.classList.add('hidden');
                    if (picker) picker.classList.add('hidden');
                    if (composer) composer.classList.remove('hidden');
                }
                // Cacher typing quand un message arrive
                document.getElementById('typing-indicator')?.classList.add('hidden');
                scroll();
            }
        } catch(e) { console.error('Poll:', e); }
    }, POLL);

    // ═══ Typing indicator : agent → Chatwoot ═══
    input.addEventListener('input', function() {
        if (!isTypingSent && this.value.length > 0) {
            isTypingSent = true;
            fetch(`/ajax/conversations/${CID}/typing`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ typing_status: 'on' }),
            }).catch(() => {});
        }
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            isTypingSent = false;
            fetch(`/ajax/conversations/${CID}/typing`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ typing_status: 'off' }),
            }).catch(() => {});
        }, 3000);
    });

    // ═══ Pagination messages anciens (scroll up) ═══
    container.addEventListener('scroll', async function() {
        if (container.scrollTop < 80 && !loadingOlder && !noMoreOlder && oldestMsgId > 0) {
            loadingOlder = true;
            const sentinel = document.getElementById('load-more-sentinel');
            if (sentinel) sentinel.classList.remove('hidden');
            const prevHeight = container.scrollHeight;
            try {
                const r = await fetch(`/ajax/conversations/${CID}/messages-before?before=${oldestMsgId}`);
                const d = await r.json();
                const msgs = d.messages || [];
                if (msgs.length === 0) {
                    noMoreOlder = true;
                    if (sentinel) sentinel.innerHTML = '<span class="text-[10px] text-gray-300">Debut de la conversation</span>';
                } else {
                    msgs.reverse().forEach(m => {
                        if (m.id && displayedIds.has(m.id)) return;
                        if (m.id) displayedIds.add(m.id);
                        oldestMsgId = Math.min(oldestMsgId, m.id);
                        prependMsg(m);
                    });
                    // Maintenir la position de scroll
                    container.scrollTop = container.scrollHeight - prevHeight;
                    if (sentinel) sentinel.classList.add('hidden');
                }
            } catch(e) { console.error('LoadOlder:', e); }
            loadingOlder = false;
        }
    });

    // ═══ File handling ═══
    fileInput.addEventListener('change', function() {
        const newFiles = Array.from(this.files);
        selectedFiles = [...selectedFiles, ...newFiles].slice(0, 5);
        this.value = '';
        renderFilePreview();
    });

    function renderFilePreview() {
        if (selectedFiles.length === 0) {
            filePreview.classList.add('hidden');
            filePreview.innerHTML = '';
            return;
        }
        filePreview.classList.remove('hidden');
        filePreview.innerHTML = '';
        selectedFiles.forEach((file, idx) => {
            const el = document.createElement('div');
            el.className = 'relative group flex items-center gap-2 px-2.5 py-1.5 bg-gray-100 rounded-lg border border-gray-200';
            const isImg = file.type.startsWith('image/');
            if (isImg) {
                const img = document.createElement('img');
                img.className = 'w-10 h-10 rounded object-cover flex-shrink-0';
                img.src = URL.createObjectURL(file);
                el.appendChild(img);
            } else {
                const icon = document.createElement('div');
                icon.className = 'w-10 h-10 rounded bg-gray-200 flex items-center justify-center flex-shrink-0';
                const ext = file.name.split('.').pop().toUpperCase();
                icon.innerHTML = `<span class="text-[9px] font-bold text-gray-500">${ext}</span>`;
                el.appendChild(icon);
            }
            const info = document.createElement('div');
            info.className = 'min-w-0';
            info.innerHTML = `<p class="text-[11px] font-medium text-gray-700 truncate max-w-[120px]">${esc(file.name)}</p><p class="text-[10px] text-gray-400">${(file.size/1024).toFixed(0)} Ko</p>`;
            el.appendChild(info);
            const rm = document.createElement('button');
            rm.type = 'button';
            rm.className = 'absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-[10px] opacity-0 group-hover:opacity-100 transition shadow';
            rm.innerHTML = '&times;';
            rm.addEventListener('click', () => { selectedFiles.splice(idx, 1); renderFilePreview(); });
            el.appendChild(rm);
            filePreview.appendChild(el);
        });
    }

    function setSending(on) {
        const btn = document.getElementById('send-btn');
        const icon = document.getElementById('send-icon');
        const loader = document.getElementById('send-loader');
        btn.disabled = on;
        if (on) {
            btn.classList.add('opacity-70');
            icon.classList.add('hidden');
            loader.classList.remove('hidden');
        } else {
            btn.classList.remove('opacity-70');
            icon.classList.remove('hidden');
            loader.classList.add('hidden');
        }
    }

    async function doSend(isPrivate) {
        const c = input.value.trim();
        if (!c && selectedFiles.length === 0) return;
        hideCanned();
        setSending(true);

        try {
            let body, headers;
            if (selectedFiles.length > 0) {
                const fd = new FormData();
                if (c) fd.append('content', c);
                fd.append('is_private', isPrivate ? '1' : '0');
                selectedFiles.forEach(f => fd.append('attachments[]', f));
                body = fd;
                headers = { 'X-CSRF-TOKEN': TOKEN };
            } else {
                body = JSON.stringify({ content: c, is_private: isPrivate });
                headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN };
            }
            headers['Accept'] = 'application/json';
            const r = await fetch(`/ajax/conversations/${CID}/messages`, { method: 'POST', headers, body });
            if (!r.ok) {
                const err = await r.json().catch(() => null);
                const msg = err?.message || err?.error || `Erreur ${r.status}`;
                showPanelToast(msg, true);
                return;
            }
            const m = await r.json();
            if (!m || !m.id) {
                showPanelToast('Réponse inattendue du serveur', true);
                return;
            }
            // Effacer input et fichiers seulement après succès confirmé
            input.value = '';
            selectedFiles = [];
            renderFilePreview();
            appendMsg(m);
            lastId = Math.max(lastId, m.id);
            scroll();
            // Mettre a jour la sidebar : remonter la conversation en haut avec le dernier message
            if (window._ensureConvInSidebar) {
                window._ensureConvInSidebar(CID, {
                    lastMessage: m.content || 'Piece jointe',
                    contactName: '{{ addslashes($conversation->contactName) }}',
                    contactThumbnail: '{{ $contact["thumbnail"] ?? "" }}',
                });
            }
        } catch(e) {
            console.error('Send:', e);
            showPanelToast('Erreur réseau — vérifiez votre connexion et réessayez', true);
        } finally {
            setSending(false);
            input.focus();
        }
    }

    // ═══ Send message ═══
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await doSend(false);
    });

    // ═══ Private note ═══
    window.sendPrivateNote = async function() {
        await doSend(true);
    };

    // ═══ Delete message ═══
    window.deleteMsg = async function(msgId) {
        if (!confirm('Supprimer ce message ?')) return;
        try {
            const r = await fetch(`/ajax/conversations/${CID}/messages/${msgId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': TOKEN },
            });
            if (r.ok) {
                const el = document.querySelector(`[data-msg-id="${msgId}"]`);
                if (el) el.remove();
                showPanelToast('Message supprime');
            }
        } catch(e) { console.error('Delete msg:', e); }
    };

    // ═══ Status ═══
    window.toggleStatus = async function(action) {
        try {
            const r = await fetch(`/ajax/conversations/${CID}/status`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ action }),
            });
            const result = await r.json();
            const newStatus = result.current_status || (action === 'resolve' ? 'resolved' : 'open');

            // Mettre a jour/ajouter la conversation dans la sidebar
            if (window._ensureConvInSidebar) {
                window._ensureConvInSidebar(CID, {
                    status: newStatus,
                    contactName: '{{ addslashes($conversation->contactName) }}',
                    contactThumbnail: '{{ $contact["thumbnail"] ?? "" }}',
                });
            }

            window.loadConversation(CID);
        } catch(e) { console.error('Status:', e); }
    };

    // ═══ Delete conversation ═══
    window.deleteConversation = async function() {
        if (!confirm('Supprimer definitivement cette conversation ? Cette action est irreversible.')) return;
        try {
            const r = await fetch(`/ajax/conversations/${CID}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': TOKEN },
            });
            if (r.ok) {
                // Retirer de la sidebar
                if (window._removeConvFromSidebar) window._removeConvFromSidebar(CID);
                // Remettre le placeholder dans la zone chat
                const zone = document.getElementById('chat-zone');
                if (zone) {
                    zone.innerHTML = '<div class="flex-1 flex items-center justify-center"><div class="text-center text-gray-400"><svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg><p class="text-lg font-medium">Conversation supprimee</p><p class="text-sm mt-1">Selectionnez une autre conversation</p></div></div>';
                }
                // Arreter le polling de cette conversation
                if (window._chatPollTimer) clearInterval(window._chatPollTimer);
            } else {
                const err = await r.json().catch(() => null);
                alert('Erreur: ' + (err?.error || 'Impossible de supprimer'));
            }
        } catch(e) {
            console.error('Delete conv:', e);
            alert('Erreur de suppression');
        }
    };

    // ═══ Assign agent ═══
    assignSel.addEventListener('change', async function() {
        const val = this.value;
        try {
            await fetch(`/ajax/conversations/${CID}/assign`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ agent_id: val ? parseInt(val) : 0 }),
            });
            showPanelToast(val ? 'Agent assigne' : 'Agent desassigne');
        } catch(e) { console.error('Assign:', e); }
    });

    // ═══ Assign team ═══
    teamSel.addEventListener('change', async function() {
        const val = this.value;
        try {
            await fetch(`/ajax/conversations/${CID}/team`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ team_id: val ? parseInt(val) : 0 }),
            });
            showPanelToast(val ? 'Equipe assignee' : 'Equipe desassignee');
        } catch(e) { console.error('Team:', e); }
    });

    // ═══ Load teams ═══
    (async function loadTeams() {
        try {
            const r = await fetch('/ajax/teams');
            const teams = await r.json();
            teams.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                teamSel.appendChild(opt);
            });
        } catch(e) {}
    })();

    // ═══ Canned Responses Autocomplete ═══
    (async function loadCanned() {
        try {
            const r = await fetch('/ajax/canned-responses');
            cannedResponses = await r.json();
        } catch(e) {}
    })();

    input.addEventListener('input', function() {
        const val = this.value;
        if (val.startsWith('/') && val.length > 1) {
            const search = val.substring(1).toLowerCase();
            const matches = cannedResponses.filter(c =>
                c.short_code.toLowerCase().includes(search) || c.content.toLowerCase().includes(search)
            );
            if (matches.length > 0) {
                showCanned(matches);
            } else {
                hideCanned();
            }
        } else if (val === '/') {
            showCanned(cannedResponses);
        } else {
            hideCanned();
        }
    });

    input.addEventListener('keydown', function(e) {
        const dropdown = document.getElementById('canned-dropdown');
        if (dropdown.classList.contains('hidden')) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
            return;
        }
        const items = dropdown.querySelectorAll('.canned-item');
        const active = dropdown.querySelector('.canned-item.bg-primary-50');
        let idx = Array.from(items).indexOf(active);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (active) active.classList.remove('bg-primary-50');
            idx = (idx + 1) % items.length;
            items[idx].classList.add('bg-primary-50');
            items[idx].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (active) active.classList.remove('bg-primary-50');
            idx = idx <= 0 ? items.length - 1 : idx - 1;
            items[idx].classList.add('bg-primary-50');
            items[idx].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (active) active.click();
            else if (items.length > 0) items[0].click();
        } else if (e.key === 'Escape') {
            hideCanned();
        }
    });

    function showCanned(matches) {
        const dropdown = document.getElementById('canned-dropdown');
        const list = document.getElementById('canned-list');
        list.innerHTML = '';
        matches.slice(0, 8).forEach((c, i) => {
            const div = document.createElement('div');
            div.className = `canned-item px-3 py-2 cursor-pointer hover:bg-primary-50 transition ${i === 0 ? 'bg-primary-50' : ''}`;
            div.innerHTML = `<div class="flex items-center gap-2"><span class="text-[11px] font-mono font-semibold text-primary-600 bg-primary-50 px-1.5 py-0.5 rounded">/${esc(c.short_code)}</span><span class="text-xs text-gray-500 truncate">${esc(c.content.substring(0, 60))}${c.content.length > 60 ? '...' : ''}</span></div>`;
            div.addEventListener('click', () => {
                input.value = c.content;
                hideCanned();
                input.focus();
            });
            list.appendChild(div);
        });
        dropdown.classList.remove('hidden');
    }

    function hideCanned() {
        document.getElementById('canned-dropdown').classList.add('hidden');
    }

    // ═══ Labels ═══
    let labelColorMap = {};

    (async function loadAllLabels() {
        try {
            const r = await fetch('/ajax/labels');
            allLabels = await r.json();
            // Build color map from API response
            allLabels.forEach(l => {
                if (l.title && l.color) labelColorMap[l.title] = l.color;
            });
            renderLabelOptions();
            renderLabelsBar(); // Re-render with colors
        } catch(e) {}
    })();

    window.toggleLabelDropdown = function() {
        const dd = document.getElementById('label-dropdown');
        dd.classList.toggle('hidden');
        if (!dd.classList.contains('hidden')) {
            document.getElementById('label-search').value = '';
            renderLabelOptions();
            document.getElementById('label-search').focus();
        }
    };

    window.filterLabels = function() {
        renderLabelOptions();
    };

    window.handleLabelKeydown = function(e) {
        if (e.key === 'Escape') {
            document.getElementById('label-dropdown').classList.add('hidden');
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const search = document.getElementById('label-search').value.trim().toLowerCase();
            if (!search) return;
            // Check if exact match exists in filtered options
            const exactMatch = allLabels.find(l => (l.title || l).toLowerCase() === search);
            if (exactMatch && !currentLabels.includes(exactMatch.title || exactMatch)) {
                addLabel(exactMatch.title || exactMatch);
            } else if (!currentLabels.includes(search)) {
                createAndAddLabel();
            }
        }
    };

    function renderLabelOptions() {
        const search = (document.getElementById('label-search')?.value || '').toLowerCase().trim();
        const opts = document.getElementById('label-options');
        const createOpt = document.getElementById('label-create-option');
        opts.innerHTML = '';

        const filtered = allLabels.filter(l => {
            const title = l.title || l;
            return title.toLowerCase().includes(search) && !currentLabels.includes(title);
        });

        // Show create option if search text doesn't match any existing label exactly
        const exactExists = allLabels.some(l => (l.title || l).toLowerCase() === search);
        const alreadyAdded = currentLabels.some(l => l.toLowerCase() === search);
        if (search && !exactExists && !alreadyAdded) {
            createOpt.classList.remove('hidden');
            document.getElementById('label-create-name').textContent = search;
        } else {
            createOpt.classList.add('hidden');
        }

        if (filtered.length === 0 && !search) {
            opts.innerHTML = '<p class="px-3 py-2 text-xs text-gray-400">Aucun label disponible</p>';
            return;
        }

        filtered.forEach(l => {
            const title = l.title || l;
            const color = l.color || null;
            const div = document.createElement('div');
            div.className = 'px-3 py-1.5 text-xs text-gray-700 cursor-pointer hover:bg-primary-50 transition flex items-center gap-2';
            if (color) {
                div.innerHTML = `<span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:${esc(color)}"></span>${esc(title)}`;
            } else {
                div.textContent = title;
            }
            div.addEventListener('click', () => addLabel(title));
            opts.appendChild(div);
        });
    }

    window.createAndAddLabel = async function() {
        const search = document.getElementById('label-search').value.trim();
        if (!search) return;
        // Normalize: lowercase, replace spaces with underscores (Chatwoot convention)
        const normalized = search.toLowerCase().replace(/\s+/g, '_');
        try {
            const r = await fetch('/ajax/labels', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ title: normalized }),
            });
            if (!r.ok) {
                const errData = await r.json().catch(() => null);
                // If label already exists (409 or 422), just add it to the conversation
                if (r.status === 422 || r.status === 409) {
                    if (!currentLabels.includes(normalized)) {
                        currentLabels.push(normalized);
                        await syncLabels();
                        document.getElementById('label-dropdown').classList.add('hidden');
                        showPanelToast('Label "' + normalized + '" ajoute');
                    }
                    return;
                }
                throw new Error(errData?.message || errData?.error || 'Erreur API');
            }
            const newLabel = await r.json();
            // Response might be wrapped in payload
            const labelData = newLabel.payload || newLabel;
            // Add to local cache
            allLabels.push(labelData);
            if (labelData.color) labelColorMap[labelData.title || normalized] = labelData.color;
            // Add to conversation
            const labelTitle = labelData.title || normalized;
            if (!currentLabels.includes(labelTitle)) {
                currentLabels.push(labelTitle);
            }
            await syncLabels();
            document.getElementById('label-dropdown').classList.add('hidden');
            showPanelToast('Label "' + labelTitle + '" cree et ajoute');
        } catch(e) {
            console.error('Create label:', e);
            showPanelToast('Erreur: ' + e.message);
        }
    };

    async function addLabel(label) {
        if (currentLabels.includes(label)) {
            document.getElementById('label-dropdown').classList.add('hidden');
            return;
        }
        currentLabels.push(label);
        await syncLabels();
        document.getElementById('label-dropdown').classList.add('hidden');
        showPanelToast('Label "' + label + '" ajoute');
    }

    window.removeLabel = async function(label) {
        currentLabels = currentLabels.filter(l => l !== label);
        await syncLabels();
        showPanelToast('Label retire');
    };

    async function syncLabels() {
        renderLabelsBar();
        renderLabelOptions();
        try {
            const r = await fetch(`/ajax/conversations/${CID}/labels`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ labels: currentLabels }),
            });
            if (!r.ok) {
                const err = await r.json().catch(() => null);
                console.error('Sync labels error:', r.status, err);
                showPanelToast('Erreur sync labels: ' + (err?.message || r.status));
            }
        } catch(e) { console.error('Labels:', e); }
    }

    function renderLabelsBar() {
        const container = document.getElementById('labels-container');
        container.innerHTML = '';
        currentLabels.forEach(label => {
            const color = labelColorMap[label];
            const span = document.createElement('span');
            if (color) {
                span.className = 'conv-label inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium border';
                span.style.backgroundColor = color + '18';
                span.style.color = color;
                span.style.borderColor = color + '40';
            } else {
                span.className = 'conv-label inline-flex items-center gap-1 px-2 py-0.5 bg-primary-50 text-primary-700 rounded-full text-[10px] font-medium';
            }
            span.innerHTML = `${esc(label)} <button onclick="removeLabel('${esc(label)}')" class="hover:text-red-500 transition">&times;</button>`;
            container.appendChild(span);
        });
    }

    // ═══ Contact Sidebar ═══
    window.toggleContactSidebar = function() {
        const sidebar = document.getElementById('contact-sidebar');
        if (sidebar.classList.contains('hidden')) {
            sidebar.classList.remove('hidden');
            sidebar.classList.add('flex');
            loadContactConvos();
            loadContactNotes();
        } else {
            sidebar.classList.add('hidden');
            sidebar.classList.remove('flex');
        }
    };

    window.showContactTab = function(tab) {
        document.querySelectorAll('.contact-tab').forEach(el => {
            el.classList.remove('border-primary-500', 'text-primary-700');
            el.classList.add('border-transparent', 'text-gray-500');
        });
        document.querySelectorAll('.contact-tab-content').forEach(el => el.classList.add('hidden'));
        const btn = document.querySelector(`.contact-tab[data-tab="${tab}"]`);
        btn.classList.add('border-primary-500', 'text-primary-700');
        btn.classList.remove('border-transparent', 'text-gray-500');
        document.getElementById(`tab-${tab}`).classList.remove('hidden');

        if (tab === 'convos') loadContactConvos();
        if (tab === 'notes') loadContactNotes();
    };

    async function loadContactConvos() {
        if (!CONTACT_ID) return;
        const list = document.getElementById('contact-convos-list');
        list.innerHTML = '<div class="text-center py-4"><span class="text-xs text-gray-400">Chargement...</span></div>';
        try {
            const r = await fetch(`/ajax/contacts/${CONTACT_ID}/conversations`);
            const data = await r.json();
            const convos = data.payload || data;
            if (!convos || !convos.length) {
                list.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">Aucun historique</p>';
                return;
            }
            list.innerHTML = '';
            convos.forEach(c => {
                const isCurrent = c.id == CID;
                const msgs = c.messages || [];
                const _sClasses = {'open':'bg-blue-50 text-blue-700','pending':'bg-amber-50 text-amber-700','resolved':'bg-green-50 text-green-700','snoozed':'bg-gray-100 text-gray-600'};
                const _sLabels = {'open':'Ouvert','pending':'En attente','resolved':'Résolu','snoozed':'Reporté'};
                const statusClass = _sClasses[c.status] || 'bg-gray-100 text-gray-600';
                const statusLabel = _sLabels[c.status] || c.status;
                const date = c.created_at ? new Date(typeof c.created_at === 'number' ? c.created_at * 1000 : c.created_at).toLocaleDateString('fr-FR', {day:'2-digit', month:'short', year:'numeric'}) : '';

                const card = document.createElement('div');
                card.className = `rounded-xl border overflow-hidden mb-3 ${isCurrent ? 'border-primary-300 ring-1 ring-primary-200' : 'border-gray-200'}`;

                // Header conversation
                const header = document.createElement('div');
                header.className = `px-3 py-2.5 flex items-center justify-between cursor-pointer ${isCurrent ? 'bg-primary-50' : 'bg-gray-50 hover:bg-gray-100'} transition`;
                header.innerHTML = `
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-semibold text-[11px] ${isCurrent ? 'text-primary-700' : 'text-gray-700'}">#${c.id}</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[9px] font-medium ${statusClass}">${statusLabel}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-gray-400">${date}</span>
                        <svg class="conv-chevron w-3.5 h-3.5 text-gray-400 transition-transform ${isCurrent ? 'rotate-180' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>`;
                card.appendChild(header);

                // Messages container (expandable)
                const msgsDiv = document.createElement('div');
                msgsDiv.className = `border-t border-gray-100 px-3 py-2 space-y-1.5 max-h-60 overflow-y-auto scrollbar-thin ${isCurrent ? '' : 'hidden'}`;

                const displayMsgs = msgs.filter(m => m.message_type !== 2).slice(-15);
                if (displayMsgs.length === 0) {
                    msgsDiv.innerHTML = '<p class="text-[10px] text-gray-400 text-center py-2">Aucun message</p>';
                } else {
                    displayMsgs.forEach(m => {
                        const isOut = m.message_type === 1;
                        const isPriv = m.private || false;
                        const msgEl = document.createElement('div');
                        msgEl.className = `flex ${isOut ? 'justify-end' : 'justify-start'}`;
                        const bubbleBg = isPriv ? 'bg-amber-50 border border-amber-200 text-amber-800' : (isOut ? 'bg-primary-500 text-white' : 'bg-white border border-gray-200 text-gray-700');
                        const timeC = isPriv ? 'text-amber-400' : (isOut ? 'text-primary-200' : 'text-gray-400');
                        const time = m.created_at ? new Date(typeof m.created_at === 'number' ? m.created_at * 1000 : m.created_at).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}) : '';
                        const privTag = isPriv ? '<span class="text-[8px] font-bold text-amber-500 block mb-0.5">NOTE</span>' : '';

                        let contentHtml = '';
                        if (m.content) contentHtml = `<p class="text-[11px] leading-relaxed whitespace-pre-wrap">${esc(m.content)}</p>`;

                        // Attachments dans l'historique
                        let attHtml = '';
                        if (m.attachments && m.attachments.length) {
                            attHtml = m.attachments.map(a => {
                                if (a.file_type === 'image') return `<img src="${a.data_url}" class="max-w-full rounded max-h-20 mt-1" loading="lazy">`;
                                return `<span class="text-[10px] underline">${esc(a.file_name || 'Fichier')}</span>`;
                            }).join('');
                        }

                        msgEl.innerHTML = `<div class="max-w-[85%] ${bubbleBg} rounded-xl px-2.5 py-1.5 shadow-sm">${privTag}${contentHtml}${attHtml}<p class="text-[9px] mt-0.5 ${timeC}">${time}</p></div>`;
                        msgsDiv.appendChild(msgEl);
                    });
                }
                card.appendChild(msgsDiv);

                // Toggle expand + navigate
                header.addEventListener('click', () => {
                    const chevron = header.querySelector('.conv-chevron');
                    if (msgsDiv.classList.contains('hidden')) {
                        msgsDiv.classList.remove('hidden');
                        chevron.classList.add('rotate-180');
                        // Charger les messages si vides et pas la conversation courante
                        if (!isCurrent && displayMsgs.length === 0) {
                            loadConvoMessages(c.id, msgsDiv);
                        }
                    } else {
                        msgsDiv.classList.add('hidden');
                        chevron.classList.remove('rotate-180');
                    }
                });

                // Bouton pour ouvrir la conversation
                if (!isCurrent) {
                    const openBtn = document.createElement('div');
                    openBtn.className = 'px-3 py-2 border-t border-gray-100 bg-gray-50';
                    openBtn.innerHTML = `<button class="w-full text-[10px] font-medium text-primary-600 hover:text-primary-700 transition">Ouvrir cette conversation</button>`;
                    openBtn.querySelector('button').addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (window.loadConversation) window.loadConversation(c.id);
                    });
                    card.appendChild(openBtn);
                }

                list.appendChild(card);
            });
        } catch(e) {
            console.error('History:', e);
            list.innerHTML = '<p class="text-xs text-red-400 text-center py-4">Erreur de chargement</p>';
        }
    }

    async function loadConvoMessages(convoId, container) {
        try {
            const r = await fetch(`/ajax/conversations/${convoId}/poll?last_message_id=0`);
            const data = await r.json();
            const msgs = (data.messages || []).filter(m => m.message_type !== 2).slice(-15);
            if (!msgs.length) {
                container.innerHTML = '<p class="text-[10px] text-gray-400 text-center py-2">Aucun message</p>';
                return;
            }
            container.innerHTML = '';
            msgs.forEach(m => {
                const isOut = m.message_type === 1;
                const isPriv = m.private || false;
                const el = document.createElement('div');
                el.className = `flex ${isOut ? 'justify-end' : 'justify-start'}`;
                const bg = isPriv ? 'bg-amber-50 border border-amber-200 text-amber-800' : (isOut ? 'bg-primary-500 text-white' : 'bg-white border border-gray-200 text-gray-700');
                const tc = isPriv ? 'text-amber-400' : (isOut ? 'text-primary-200' : 'text-gray-400');
                const time = m.created_at ? new Date(m.created_at * 1000).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}) : '';
                el.innerHTML = `<div class="max-w-[85%] ${bg} rounded-xl px-2.5 py-1.5 shadow-sm">${m.content ? `<p class="text-[11px] leading-relaxed whitespace-pre-wrap">${esc(m.content)}</p>` : ''}${(m.attachments||[]).map(a => a.file_type==='image' ? `<img src="${a.data_url}" class="max-w-full rounded max-h-20 mt-1">` : `<span class="text-[10px] underline">${esc(a.file_name||'Fichier')}</span>`).join('')}<p class="text-[9px] mt-0.5 ${tc}">${time}</p></div>`;
                container.appendChild(el);
            });
        } catch(e) {
            container.innerHTML = '<p class="text-[10px] text-red-400 text-center py-2">Erreur</p>';
        }
    }

    async function loadContactNotes() {
        if (!CONTACT_ID) return;
        const list = document.getElementById('contact-notes-list');
        try {
            const r = await fetch(`/ajax/contacts/${CONTACT_ID}/notes`);
            const notes = await r.json();
            const payload = notes.payload || notes;
            if (!payload.length) {
                list.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">Aucune note</p>';
                return;
            }
            list.innerHTML = '';
            payload.forEach(n => {
                const div = document.createElement('div');
                div.className = 'p-2.5 bg-gray-50 rounded-lg border border-gray-100 group';
                div.innerHTML = `<p class="text-xs text-gray-700 whitespace-pre-wrap">${esc(n.content)}</p><div class="flex items-center justify-between mt-1.5"><span class="text-[10px] text-gray-400">${n.user?.name || ''} ${n.created_at ? '&middot; ' + new Date(n.created_at).toLocaleDateString('fr-FR') : ''}</span><button onclick="deleteNote(${CONTACT_ID}, ${n.id})" class="text-[10px] text-red-400 opacity-0 group-hover:opacity-100 hover:text-red-600 transition">Supprimer</button></div>`;
                list.appendChild(div);
            });
        } catch(e) {
            list.innerHTML = '<p class="text-xs text-red-400 text-center py-4">Erreur de chargement</p>';
        }
    }

    // Note form
    document.getElementById('note-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const noteInput = document.getElementById('note-input');
        const content = noteInput.value.trim();
        if (!content || !CONTACT_ID) return;
        noteInput.value = '';
        try {
            await fetch(`/ajax/contacts/${CONTACT_ID}/notes`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                body: JSON.stringify({ content }),
            });
            loadContactNotes();
        } catch(e) { console.error('Note:', e); }
    });

    window.deleteNote = async function(contactId, noteId) {
        if (!confirm('Supprimer cette note ?')) return;
        try {
            await fetch(`/ajax/contacts/${contactId}/notes/${noteId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': TOKEN },
            });
            loadContactNotes();
        } catch(e) { console.error('Delete note:', e); }
    };

    // ═══ Helpers ═══
    function renderAttachmentsHtml(attachments, isOutgoing) {
        if (!attachments || !attachments.length) return '';
        const btnBg = isOutgoing ? 'bg-white/20 hover:bg-white/30 text-white' : 'bg-primary-100 hover:bg-primary-200 text-primary-600';
        const barBg = isOutgoing ? 'bg-white/20' : 'bg-gray-200';
        const barFill = isOutgoing ? 'bg-white' : 'bg-primary-500';
        const timC = isOutgoing ? 'text-primary-200' : 'text-gray-400';
        return '<div class="mt-1.5 space-y-1.5">' + attachments.map(att => {
            const ft = att.file_type || 'file';
            const url = att.data_url || '';
            const name = esc(att.file_name || 'Fichier');
            const size = att.file_size ? `${Math.round(att.file_size/1024)} Ko` : '';
            if (ft === 'image') {
                return `<a href="${url}" target="_blank" class="block"><img src="${url}" alt="${name}" class="max-w-full rounded-lg max-h-64 object-cover cursor-pointer hover:opacity-90 transition" loading="lazy"></a>`;
            } else if (ft === 'audio') {
                return `<div class="audio-player flex items-center gap-2 py-1 min-w-[220px] max-w-[280px]" data-src="${url}">
                    <button type="button" class="audio-play-btn w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 ${btnBg} transition">
                        <svg class="audio-icon-play w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <svg class="audio-icon-pause w-4 h-4 hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                    </button>
                    <div class="flex-1 min-w-0">
                        <div class="audio-progress-bar relative h-1.5 rounded-full ${barBg} cursor-pointer">
                            <div class="audio-progress absolute left-0 top-0 h-full rounded-full ${barFill} transition-all" style="width:0%"></div>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="audio-current text-[10px] ${timC}">0:00</span>
                            <span class="audio-duration text-[10px] ${timC}">--:--</span>
                        </div>
                    </div>
                </div>`;
            } else if (ft === 'video') {
                return `<video controls preload="metadata" class="max-w-full rounded-lg max-h-64"><source src="${url}"></video>`;
            } else {
                const fileBg = isOutgoing ? 'bg-primary-700/30 hover:bg-primary-700/50' : 'bg-gray-100 hover:bg-gray-200';
                const iconC = isOutgoing ? 'text-primary-200' : 'text-gray-500';
                const sizeC = isOutgoing ? 'text-primary-300' : 'text-gray-400';
                return `<a href="${url}" target="_blank" class="flex items-center gap-2 px-3 py-2 rounded-lg ${fileBg} transition"><svg class="w-5 h-5 flex-shrink-0 ${iconC}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><div class="min-w-0"><p class="text-xs font-medium truncate">${name}</p>${size ? `<p class="text-[10px] ${sizeC}">${size}</p>` : ''}</div></a>`;
            }
        }).join('') + '</div>';
    }

    function appendMsg(m) {
        // Dedup: ne pas afficher un message deja present
        if (m.id && displayedIds.has(m.id)) return;
        if (m.id) displayedIds.add(m.id);

        const out = m.message_type === 1;
        const act = m.message_type === 2;
        const prv = m.private || false;
        const div = document.createElement('div');

        if (act) {
            div.className = 'flex justify-center';
            div.innerHTML = `<span class="text-[11px] text-gray-400 bg-white border border-gray-100 px-3 py-1 rounded-full shadow-sm">${esc(m.content||'')}</span>`;
        } else {
            div.className = `group/msg ${out ? 'message-outgoing' : 'message-incoming'}`;
            if (m.id) div.dataset.msgId = m.id;
            const bg = prv ? 'bg-amber-50 border border-amber-200' : (out ? 'bg-primary-600 text-white' : 'bg-white border border-gray-200');
            const corner = out ? 'rounded-br-md' : 'rounded-bl-md';
            const tc = prv ? 'text-amber-400' : (out ? 'text-primary-200' : 'text-gray-400');
            const time = m.created_at ? new Date(m.created_at * 1000).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}) : '';
            const sender = (out && m.sender?.name) ? ` &middot; ${esc(m.sender.name)}` : '';
            const prvTag = prv ? '<p class="text-[10px] font-semibold text-amber-600 mb-0.5">Note privee</p>' : '';
            const contentHtml = (m.content) ? `<p class="text-[13px] leading-relaxed whitespace-pre-wrap">${esc(m.content)}</p>` : '';
            const attHtml = renderAttachmentsHtml(m.attachments, out);
            const delBtn = (out && m.id) ? `<button onclick="deleteMsg(${m.id})" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full items-center justify-center text-[10px] shadow hidden group-hover/msg:flex hover:bg-red-600 transition" title="Supprimer"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>` : '';
            div.innerHTML = `<div class="relative max-w-[80%] ${bg} rounded-2xl ${corner} px-4 py-2.5 shadow-sm">${delBtn}${prvTag}${contentHtml}${attHtml}<p class="text-[10px] mt-1 ${tc}">${time}${sender}</p></div>`;
        }
        container.appendChild(div);
        initAudioPlayers(div);
    }

    function prependMsg(m) {
        const out = m.message_type === 1;
        const act = m.message_type === 2;
        const prv = m.private || false;
        const div = document.createElement('div');
        const sentinel = document.getElementById('load-more-sentinel');

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
            const contentHtml = (m.content) ? `<p class="text-[13px] leading-relaxed whitespace-pre-wrap">${esc(m.content)}</p>` : '';
            const attHtml = renderAttachmentsHtml(m.attachments, out);
            div.innerHTML = `<div class="max-w-[80%] ${bg} rounded-2xl ${corner} px-4 py-2.5 shadow-sm">${prvTag}${contentHtml}${attHtml}<p class="text-[10px] mt-1 ${tc}">${time}${sender}</p></div>`;
        }
        if (sentinel && sentinel.nextSibling) {
            container.insertBefore(div, sentinel.nextSibling);
        } else {
            container.insertBefore(div, container.firstChild);
        }
        initAudioPlayers(div);
    }

    function scroll() { container.scrollTop = container.scrollHeight; }
    function esc(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    function fmtTime(s) { const m = Math.floor(s/60); return m + ':' + String(Math.floor(s%60)).padStart(2,'0'); }

    // ═══ Custom Audio Player (WhatsApp style) ═══
    let activeAudio = null;
    function initAudioPlayers(root) {
        root.querySelectorAll('.audio-player:not([data-init])').forEach(player => {
            player.setAttribute('data-init', '1');
            const src = player.dataset.src;
            const playBtn = player.querySelector('.audio-play-btn');
            const iconPlay = player.querySelector('.audio-icon-play');
            const iconPause = player.querySelector('.audio-icon-pause');
            const progressBar = player.querySelector('.audio-progress-bar');
            const progress = player.querySelector('.audio-progress');
            const currentEl = player.querySelector('.audio-current');
            const durationEl = player.querySelector('.audio-duration');
            let audio = null;

            function ensureAudio() {
                if (audio) return audio;
                audio = new Audio(src);
                audio.preload = 'metadata';
                audio.addEventListener('loadedmetadata', () => {
                    durationEl.textContent = fmtTime(audio.duration);
                });
                audio.addEventListener('timeupdate', () => {
                    if (!audio.duration) return;
                    const pct = (audio.currentTime / audio.duration) * 100;
                    progress.style.width = pct + '%';
                    currentEl.textContent = fmtTime(audio.currentTime);
                });
                audio.addEventListener('ended', () => {
                    iconPlay.classList.remove('hidden');
                    iconPause.classList.add('hidden');
                    progress.style.width = '0%';
                    currentEl.textContent = '0:00';
                    activeAudio = null;
                });
                return audio;
            }

            playBtn.addEventListener('click', () => {
                ensureAudio();
                if (audio.paused) {
                    // Pause tout autre audio
                    if (activeAudio && activeAudio !== audio) {
                        activeAudio.pause();
                        const prev = activeAudio._playerEl;
                        if (prev) {
                            prev.querySelector('.audio-icon-play').classList.remove('hidden');
                            prev.querySelector('.audio-icon-pause').classList.add('hidden');
                        }
                    }
                    audio._playerEl = player;
                    audio.play();
                    activeAudio = audio;
                    iconPlay.classList.add('hidden');
                    iconPause.classList.remove('hidden');
                } else {
                    audio.pause();
                    iconPlay.classList.remove('hidden');
                    iconPause.classList.add('hidden');
                }
            });

            // Click sur la barre de progression pour seek
            progressBar.addEventListener('click', (e) => {
                ensureAudio();
                if (!audio.duration) return;
                const rect = progressBar.getBoundingClientRect();
                const pct = (e.clientX - rect.left) / rect.width;
                audio.currentTime = pct * audio.duration;
                progress.style.width = (pct * 100) + '%';
            });
        });
    }

    // Init audio players pour les messages deja charges (Blade)
    initAudioPlayers(container);

    // Close label dropdown on outside click
    document.addEventListener('click', function(e) {
        const dd = document.getElementById('label-dropdown');
        if (dd && !dd.classList.contains('hidden') && !e.target.closest('#label-dropdown') && !e.target.closest('[onclick*="toggleLabelDropdown"]')) {
            dd.classList.add('hidden');
        }
    });

    scroll();
    input.focus();
})();
</script>
