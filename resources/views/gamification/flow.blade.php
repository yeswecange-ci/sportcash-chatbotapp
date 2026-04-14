@extends('layouts.app')

@section('title', 'Flow — ' . $game->name)

@section('content')
<div class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('gamification.show', $game->slug) }}"
                   class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-200 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Flow Studio</h1>
                    <p class="text-sm text-gray-500">{{ $game->name }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('gamification.flow', $game->slug) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Regénérer
                </a>
                <button onclick="copyFlow()"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-violet-600 rounded-lg hover:bg-violet-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span id="copy-label">Copier le JSON</span>
                </button>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 p-1 bg-white border border-gray-200 rounded-xl mb-5 w-fit">
            <button type="button" onclick="switchView('schema')" id="btn-schema"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-violet-600 text-white transition">
                Schéma
            </button>
            <button type="button" onclick="switchView('json')" id="btn-json"
                    class="px-4 py-2 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-100 transition">
                JSON
            </button>
        </div>

        {{-- ═══ VUE SCHÉMA ═══════════════════════════════════════════════════════════ --}}
        <div id="view-schema">

            {{-- Endpoints --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Endpoints webhook</h2>
                <div class="overflow-hidden rounded-lg border border-gray-100">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">Méthode</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">URL</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rôle</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Widget</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach([
                                ['POST', '/check',     'Vérifie éligibilité + démarre participation', 'check_participant'],
                                ['POST', '/save-name', 'Enregistre le nom du participant',             'save_name'],
                                ['POST', '/answer',    'Enregistre une réponse à une question',        'answer_qN'],
                                ['POST', '/complete',  'Marque la participation comme complétée',      'http_complete'],
                            ] as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="text-xs font-mono font-bold px-2 py-0.5 bg-blue-50 text-blue-700 rounded">{{ $row[0] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <code class="text-xs font-mono text-gray-700 select-all">{{ $webhookBase }}{{ $row[1] }}</code>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $row[2] }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-mono font-medium text-violet-600">{{ $row[3] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Schéma des widgets --}}
            @php
                $typeLabels = [
                    'trigger'               => 'Déclencheur',
                    'make-http-request'     => 'Requête HTTP',
                    'send-and-wait-for-reply' => 'Envoi + Attente',
                    'send-message'          => 'Envoi message',
                    'split-based-on'        => 'Branchement',
                ];
                $typeStyle = [
                    'trigger'               => ['pill' => 'bg-gray-100 text-gray-600',   'dot' => 'bg-gray-400',   'border' => 'border-gray-200',   'line' => 'border-gray-300'],
                    'make-http-request'     => ['pill' => 'bg-blue-100 text-blue-700',   'dot' => 'bg-blue-500',   'border' => 'border-blue-200',   'line' => 'border-blue-300'],
                    'send-and-wait-for-reply' => ['pill' => 'bg-violet-100 text-violet-700', 'dot' => 'bg-violet-500', 'border' => 'border-violet-200', 'line' => 'border-violet-300'],
                    'send-message'          => ['pill' => 'bg-green-100 text-green-700', 'dot' => 'bg-green-500',  'border' => 'border-green-200',  'line' => 'border-green-300'],
                    'split-based-on'        => ['pill' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-amber-500', 'border' => 'border-amber-200',  'line' => 'border-amber-300'],
                ];
            @endphp

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-5">
                    Schéma du flow
                    <span class="ml-2 text-xs font-normal text-gray-400">({{ count($flow['states']) }} widgets)</span>
                </h2>

                <div class="relative">
                    @foreach($flow['states'] as $i => $state)
                    @php
                        $type   = $state['type'];
                        $style  = $typeStyle[$type] ?? $typeStyle['trigger'];
                        $label  = $typeLabels[$type] ?? $type;
                        $props  = $state['properties'] ?? [];
                        $trans  = $state['transitions'] ?? [];
                    @endphp

                    {{-- Connecteur entre widgets --}}
                    @if($i > 0)
                    <div class="flex items-center justify-center my-1">
                        <div class="flex flex-col items-center">
                            <div class="w-px h-4 bg-gray-200"></div>
                            <svg class="w-3 h-3 text-gray-300 -mt-px" fill="currentColor" viewBox="0 0 10 10">
                                <path d="M5 8L1 3h8z"/>
                            </svg>
                        </div>
                    </div>
                    @endif

                    {{-- Carte widget --}}
                    <div class="rounded-xl border-2 {{ $style['border'] }} bg-white overflow-hidden">

                        {{-- Titre widget --}}
                        <div class="flex items-center gap-2.5 px-4 py-3 bg-gray-50 border-b {{ $style['border'] }}">
                            <span class="w-2.5 h-2.5 rounded-full {{ $style['dot'] }} flex-shrink-0"></span>
                            <span class="font-mono text-sm font-bold text-gray-800">{{ $state['name'] }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $style['pill'] }}">{{ $label }}</span>
                            <span class="ml-auto text-xs text-gray-300 font-mono">{{ $type }}</span>
                        </div>

                        <div class="px-4 py-3 grid grid-cols-2 gap-4">

                            {{-- Colonne gauche : propriétés --}}
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Propriétés</p>

                                @if($type === 'trigger')
                                    <p class="text-xs text-gray-500">Point d'entrée du flow — aucune configuration requise.</p>

                                @elseif($type === 'make-http-request')
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-mono font-bold px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded flex-shrink-0">{{ $props['method'] ?? 'POST' }}</span>
                                            <code class="text-xs font-mono text-gray-700 break-all">{{ $props['url'] ?? '' }}</code>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <span class="font-medium">Content-Type:</span>
                                            <span class="font-mono">{{ $props['content_type'] ?? '' }}</span>
                                        </div>
                                        @if(!empty($props['parameters']))
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 mb-1">Paramètres :</p>
                                            <div class="space-y-1 pl-2 border-l-2 border-blue-100">
                                                @foreach($props['parameters'] as $param)
                                                <p class="text-xs font-mono">
                                                    <span class="text-blue-600 font-semibold">{{ $param['key'] }}</span>
                                                    <span class="text-gray-400"> = </span>
                                                    <span class="text-gray-600">{{ $param['value'] }}</span>
                                                </p>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                @elseif($type === 'send-and-wait-for-reply' || $type === 'send-message')
                                    <div class="space-y-2">
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 mb-1">Message :</p>
                                            <div class="text-xs text-gray-700 bg-gray-50 border border-gray-100 rounded-lg px-3 py-2 whitespace-pre-wrap leading-relaxed">{{ $props['body'] ?? '' }}</div>
                                        </div>
                                        @if(isset($props['timeout']))
                                        <p class="text-xs text-gray-500">
                                            <span class="font-medium">Timeout :</span> {{ $props['timeout'] }} s
                                        </p>
                                        @endif
                                        <p class="text-xs text-gray-400 font-mono">
                                            From: {{ $props['from'] ?? '' }}<br>
                                            To: {{ $props['to'] ?? '' }}
                                        </p>
                                    </div>

                                @elseif($type === 'split-based-on')
                                    <div class="space-y-2">
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 mb-1">Variable testée :</p>
                                            <code class="text-xs font-mono bg-amber-50 border border-amber-100 px-2 py-1 rounded text-amber-800 break-all">{{ $props['input'] ?? '' }}</code>
                                        </div>
                                        @foreach($trans as $tr)
                                            @if(isset($tr['conditions']))
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 mb-1">Condition (match) :</p>
                                                @foreach($tr['conditions'] as $cond)
                                                <div class="text-xs font-mono bg-gray-50 border border-gray-100 rounded px-2 py-1 space-y-0.5">
                                                    <p><span class="text-gray-400">type :</span> {{ $cond['type'] ?? '' }}</p>
                                                    <p><span class="text-gray-400">value :</span> <span class="text-amber-700 font-semibold">"{{ $cond['value'] ?? '' }}"</span></p>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>

                                @endif
                            </div>

                            {{-- Colonne droite : transitions --}}
                            <div class="border-l border-gray-100 pl-4">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Transitions</p>
                                <div class="space-y-2">
                                    @foreach($trans as $tr)
                                    <div class="flex items-start gap-2">
                                        <div class="flex-shrink-0 mt-0.5">
                                            @if($tr['event'] === 'success')
                                                <span class="inline-block w-2 h-2 rounded-full bg-green-400 mt-0.5"></span>
                                            @elseif($tr['event'] === 'failed')
                                                <span class="inline-block w-2 h-2 rounded-full bg-red-400 mt-0.5"></span>
                                            @elseif($tr['event'] === 'match')
                                                <span class="inline-block w-2 h-2 rounded-full bg-amber-400 mt-0.5"></span>
                                            @elseif($tr['event'] === 'noMatch')
                                                <span class="inline-block w-2 h-2 rounded-full bg-gray-300 mt-0.5"></span>
                                            @else
                                                <span class="inline-block w-2 h-2 rounded-full bg-violet-400 mt-0.5"></span>
                                            @endif
                                        </div>
                                        <div class="text-xs flex-1 min-w-0">
                                            <span class="font-mono text-gray-500">{{ $tr['event'] }}</span>
                                            @if(isset($tr['conditions']) && !empty($tr['conditions']))
                                                <span class="text-gray-300 mx-1">|</span>
                                                <span class="text-amber-600 font-mono">"{{ $tr['conditions'][0]['value'] ?? '' }}"</span>
                                            @endif
                                            <span class="text-gray-300 mx-1">→</span>
                                            @if(isset($tr['next']))
                                                <span class="font-mono font-semibold text-gray-800">{{ $tr['next'] }}</span>
                                            @else
                                                <span class="text-gray-400 italic">fin</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>

                    @endforeach
                </div>
            </div>

        </div>{{-- /view-schema --}}


        {{-- ═══ VUE JSON ══════════════════════════════════════════════════════════════ --}}
        <div id="view-json" class="hidden">

            {{-- Instructions --}}
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 mb-5">
                <h2 class="text-sm font-semibold text-indigo-800 mb-3">Comment importer ce flow dans Studio</h2>
                <ol class="space-y-2">
                    @foreach([
                        'Copiez le JSON ci-dessous avec le bouton "Copier le JSON"',
                        'Ouvrez Studio → <em>Flows</em> → <em>Create new Flow</em>',
                        'Choisissez "Import from JSON" et collez le JSON copié',
                        'Cliquez sur <em>Next</em> puis <em>Publish</em>',
                        'Associez ce flow à votre numéro WhatsApp',
                    ] as $step_i => $step)
                    <li class="flex items-start gap-2.5 text-sm text-indigo-700">
                        <span class="w-5 h-5 rounded-full bg-indigo-200 text-indigo-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">{{ $step_i + 1 }}</span>
                        <span>{!! $step !!}</span>
                    </li>
                    @endforeach
                </ol>
            </div>

            {{-- URLs des webhooks (rappel) --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">URLs des webhooks</h2>
                <div class="space-y-2">
                    @foreach([
                        ['POST', '/check',     'Vérification éligibilité'],
                        ['POST', '/save-name', 'Enregistrement du nom'],
                        ['POST', '/answer',    'Soumission des réponses'],
                        ['POST', '/complete',  'Complétion de la participation'],
                    ] as $wh)
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-mono font-bold text-gray-400 w-10">{{ $wh[0] }}</span>
                        <code class="flex-1 text-xs font-mono bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-lg text-gray-700 select-all">{{ $webhookBase }}{{ $wh[1] }}</code>
                        <span class="text-xs text-gray-400 flex-shrink-0">{{ $wh[2] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- JSON --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Flow JSON</span>
                    <div class="flex items-center gap-4">
                        <span class="text-xs text-gray-400">
                            Généré le {{ now()->format('d/m/Y à H:i') }}
                            @if($game->synced_at) · Dernière sync : {{ $game->synced_at->format('d/m/Y H:i') }}@endif
                        </span>
                        <button onclick="copyFlow()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-violet-600 border border-violet-200 bg-violet-50 rounded-lg hover:bg-violet-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span id="copy-label-2">Copier</span>
                        </button>
                    </div>
                </div>
                <pre id="flow-json" class="p-5 text-xs font-mono text-gray-700 overflow-x-auto max-h-[60vh] overflow-y-auto leading-relaxed">{{ json_encode($flow, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>

        </div>{{-- /view-json --}}

    </div>
</div>

@push('scripts')
<script>
function switchView(name) {
    ['schema', 'json'].forEach(function(v) {
        document.getElementById('view-' + v).classList.toggle('hidden', v !== name);
        var btn = document.getElementById('btn-' + v);
        if (v === name) {
            btn.classList.add('bg-violet-600', 'text-white');
            btn.classList.remove('text-gray-600', 'hover:bg-gray-100');
        } else {
            btn.classList.remove('bg-violet-600', 'text-white');
            btn.classList.add('text-gray-600', 'hover:bg-gray-100');
        }
    });
}

function copyFlow() {
    var json = document.getElementById('flow-json').textContent;
    navigator.clipboard.writeText(json).then(function() {
        ['copy-label', 'copy-label-2'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                var orig = el.textContent;
                el.textContent = 'Copié !';
                setTimeout(function() { el.textContent = orig; }, 2000);
            }
        });
    });
}
</script>
@endpush
@endsection
