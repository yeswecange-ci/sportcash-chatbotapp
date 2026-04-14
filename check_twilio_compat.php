<?php
/**
 * Comparaison avec le schéma Twilio Studio v2 officiel.
 * Base JSON fournie par Twilio : { "states": [...], "initial_state": "Trigger", "flags": {...} }
 *
 * On reproduit exactement ce que le service génère et on vérifie chaque règle.
 */

// ── Simulation du service ──────────────────────────────────────────────────

$webhookBase = 'https://example.com/gamification/webhook/test';

$states = [];
$x = 0; $y = 0; $step = 250;

$states[] = [
    'name' => 'Trigger', 'type' => 'trigger',
    'transitions' => [
        ['event' => 'incomingMessage',            'next' => 'check_participant'],
        ['event' => 'incomingCall'],
        ['event' => 'incomingConversationMessage', 'next' => 'check_participant'],
        ['event' => 'incomingRequest'],
        ['event' => 'incomingParent'],
    ],
    'properties' => ['offset' => ['x' => 0, 'y' => 0]],
];

$states[] = ['name' => 'check_participant', 'type' => 'make-http-request',
    'transitions' => [['event' => 'success', 'next' => 'split_eligible'], ['event' => 'failed', 'next' => 'send_not_eligible']],
    'properties' => ['offset' => ['x' => 0, 'y' => 250], 'method' => 'POST', 'url' => "$webhookBase/check",
        'body' => null, 'parameters' => [['key' => 'phone', 'value' => '{{contact.channel.address}}']], 'content_type' => 'application/x-www-form-urlencoded']];

$states[] = ['name' => 'split_eligible', 'type' => 'split-based-on',
    'transitions' => [
        ['event' => 'match', 'conditions' => [['friendly_name' => 'Is eligible', 'arguments' => ['{{widgets.check_participant.parsed.eligible}}'], 'type' => 'equal_to', 'value' => 'true']], 'next' => 'split_name_known'],
        ['event' => 'noMatch', 'next' => 'send_not_eligible'],
    ],
    'properties' => ['offset' => ['x' => 0, 'y' => 500], 'input' => '{{widgets.check_participant.parsed.eligible}}']];

$states[] = ['name' => 'send_not_eligible', 'type' => 'send-message',
    'transitions' => [['event' => 'sent']],
    'properties' => ['offset' => ['x' => 400, 'y' => 500], 'from' => '{{flow.channel.address}}', 'to' => '{{contact.channel.address}}', 'body' => "Vous n'etes pas eligible."]];

$states[] = ['name' => 'split_name_known', 'type' => 'split-based-on',
    'transitions' => [
        ['event' => 'match', 'conditions' => [['friendly_name' => 'Name known', 'arguments' => ['{{widgets.check_participant.parsed.name_known}}'], 'type' => 'equal_to', 'value' => 'true']], 'next' => 'q1'],
        ['event' => 'noMatch', 'next' => 'ask_name'],
    ],
    'properties' => ['offset' => ['x' => 0, 'y' => 750], 'input' => '{{widgets.check_participant.parsed.name_known}}']];

$states[] = ['name' => 'ask_name', 'type' => 'send-and-wait-for-reply',
    'transitions' => [['event' => 'incomingMessage', 'next' => 'save_name'], ['event' => 'timeout'], ['event' => 'delivery']],
    'properties' => ['offset' => ['x' => -350, 'y' => 1000], 'from' => '{{flow.channel.address}}', 'to' => '{{contact.channel.address}}', 'body' => 'Quel est votre nom ?', 'timeout' => 3600]];

$states[] = ['name' => 'save_name', 'type' => 'make-http-request',
    'transitions' => [['event' => 'success', 'next' => 'q1'], ['event' => 'failed']],
    'properties' => ['offset' => ['x' => -350, 'y' => 1250], 'method' => 'POST', 'url' => "$webhookBase/save-name",
        'body' => null, 'parameters' => [['key' => 'phone', 'value' => '{{contact.channel.address}}'], ['key' => 'name', 'value' => '{{widgets.ask_name.inbound.Body}}']], 'content_type' => 'application/x-www-form-urlencoded']];

$states[] = ['name' => 'q1', 'type' => 'send-and-wait-for-reply',
    'transitions' => [['event' => 'incomingMessage', 'next' => 'answer_q1'], ['event' => 'timeout'], ['event' => 'delivery']],
    'properties' => ['offset' => ['x' => 0, 'y' => 1000], 'from' => '{{flow.channel.address}}', 'to' => '{{contact.channel.address}}', 'body' => "Q1: Quelle capitale?\n\n1. Abidjan\n2. Yamoussoukro", 'timeout' => 3600]];

$states[] = ['name' => 'answer_q1', 'type' => 'make-http-request',
    'transitions' => [['event' => 'success', 'next' => 'http_complete'], ['event' => 'failed']],
    'properties' => ['offset' => ['x' => 0, 'y' => 1250], 'method' => 'POST', 'url' => "$webhookBase/answer",
        'body' => null, 'parameters' => [['key' => 'phone', 'value' => '{{contact.channel.address}}'], ['key' => 'question_order', 'value' => '1'], ['key' => 'answer', 'value' => '{{widgets.q1.inbound.Body}}']], 'content_type' => 'application/x-www-form-urlencoded']];

$states[] = ['name' => 'http_complete', 'type' => 'make-http-request',
    'transitions' => [['event' => 'success', 'next' => 'send_thank_you'], ['event' => 'failed', 'next' => 'send_thank_you']],
    'properties' => ['offset' => ['x' => 0, 'y' => 1500], 'method' => 'POST', 'url' => "$webhookBase/complete",
        'body' => null, 'parameters' => [['key' => 'phone', 'value' => '{{contact.channel.address}}']], 'content_type' => 'application/x-www-form-urlencoded']];

$states[] = ['name' => 'send_thank_you', 'type' => 'send-message',
    'transitions' => [['event' => 'sent']],
    'properties' => ['offset' => ['x' => 0, 'y' => 1750], 'from' => '{{flow.channel.address}}', 'to' => '{{contact.channel.address}}', 'body' => 'Merci !']];

$flow = ['description' => 'Flow Test', 'states' => $states, 'initial_state' => 'Trigger', 'flags' => ['allow_concurrent_calls' => true]];

// ── Validation complète ────────────────────────────────────────────────────

$allNames = array_column($states, 'name');
$errors = [];
$warnings = [];

// 1. initial_state doit référencer un state existant
if (!in_array($flow['initial_state'], $allNames)) {
    $errors[] = "initial_state '{$flow['initial_state']}' non trouvé dans states";
}

// 2. Vérifier chaque state
$validTypes = ['trigger', 'make-http-request', 'send-message', 'send-and-wait-for-reply', 'split-based-on', 'set-variables', 'run-subflow', 'send-to-flex', 'capture-payments'];
$validTriggerEvents = ['incomingMessage', 'incomingCall', 'incomingConversationMessage', 'incomingRequest', 'incomingParent'];
$validHttpEvents = ['success', 'failed'];
$validSendWaitEvents = ['incomingMessage', 'timeout', 'delivery'];
$validSendEvents = ['sent'];
$validSplitEvents = ['match', 'noMatch'];

foreach ($states as $state) {
    $n = $state['name'];
    $t = $state['type'];

    // Type valide ?
    if (!in_array($t, $validTypes)) {
        $errors[] = "[$n] type '$t' invalide";
    }

    // name: alphanumérique + underscore uniquement, ne commence pas par chiffre
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $n)) {
        $errors[] = "[$n] nom invalide (alphanum + underscore, commence par lettre/underscore)";
    }

    // Longueur max du nom
    if (strlen($n) > 64) {
        $errors[] = "[$n] nom trop long (max 64)";
    }

    // offset requis
    if (!isset($state['properties']['offset']['x']) || !isset($state['properties']['offset']['y'])) {
        $errors[] = "[$n] offset manquant ou incomplet";
    }

    // offset doit être des entiers
    if (isset($state['properties']['offset'])) {
        if (!is_int($state['properties']['offset']['x']) || !is_int($state['properties']['offset']['y'])) {
            $errors[] = "[$n] offset x/y doivent être des entiers, got " . gettype($state['properties']['offset']['x']) . "/" . gettype($state['properties']['offset']['y']);
        }
    }

    // Validations par type
    switch ($t) {
        case 'trigger':
            foreach ($state['transitions'] as $tr) {
                if (!in_array($tr['event'], $validTriggerEvents)) {
                    $errors[] = "[$n] événement trigger invalide: '{$tr['event']}'";
                }
            }
            break;

        case 'make-http-request':
            if (empty($state['properties']['url'])) $errors[] = "[$n] url manquante";
            if (empty($state['properties']['method'])) $errors[] = "[$n] method manquante";
            if (!isset($state['properties']['content_type'])) $errors[] = "[$n] content_type manquant";
            $validMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
            if (!in_array($state['properties']['method'] ?? '', $validMethods)) {
                $errors[] = "[$n] method invalide: '{$state['properties']['method']}'";
            }
            $validCt = ['application/x-www-form-urlencoded', 'application/json'];
            if (!in_array($state['properties']['content_type'] ?? '', $validCt)) {
                $errors[] = "[$n] content_type invalide: '{$state['properties']['content_type']}'";
            }
            foreach ($state['transitions'] as $tr) {
                if (!in_array($tr['event'], $validHttpEvents)) {
                    $errors[] = "[$n] événement HTTP invalide: '{$tr['event']}'";
                }
            }
            // body doit être string ou null
            if (isset($state['properties']['body']) && $state['properties']['body'] !== null && !is_string($state['properties']['body'])) {
                $errors[] = "[$n] body doit être string ou null, got " . gettype($state['properties']['body']);
            }
            break;

        case 'send-and-wait-for-reply':
            foreach (['from', 'to', 'body'] as $req) {
                if (!isset($state['properties'][$req])) $errors[] = "[$n] propriété '$req' manquante";
            }
            if (!isset($state['properties']['timeout'])) {
                $errors[] = "[$n] timeout manquant";
            } elseif (!is_int($state['properties']['timeout'])) {
                $errors[] = "[$n] timeout doit être int, got " . gettype($state['properties']['timeout']) . " = " . var_export($state['properties']['timeout'], true);
            } elseif ($state['properties']['timeout'] < 10 || $state['properties']['timeout'] > 86400) {
                $errors[] = "[$n] timeout hors limites (10-86400): {$state['properties']['timeout']}";
            }
            foreach ($state['transitions'] as $tr) {
                if (!in_array($tr['event'], $validSendWaitEvents)) {
                    $errors[] = "[$n] événement send-and-wait invalide: '{$tr['event']}'";
                }
            }
            break;

        case 'send-message':
            foreach (['from', 'to', 'body'] as $req) {
                if (!isset($state['properties'][$req])) $errors[] = "[$n] propriété '$req' manquante";
            }
            foreach ($state['transitions'] as $tr) {
                if (!in_array($tr['event'], $validSendEvents)) {
                    $errors[] = "[$n] événement send-message invalide: '{$tr['event']}'";
                }
            }
            break;

        case 'split-based-on':
            if (!isset($state['properties']['input'])) $errors[] = "[$n] propriété 'input' manquante";
            foreach ($state['transitions'] as $tr) {
                if (!in_array($tr['event'], $validSplitEvents)) {
                    $errors[] = "[$n] événement split invalide: '{$tr['event']}'";
                }
                if ($tr['event'] === 'match') {
                    if (!isset($tr['conditions']) || empty($tr['conditions'])) {
                        $errors[] = "[$n] transition 'match' sans conditions";
                    }
                    foreach ($tr['conditions'] ?? [] as $c) {
                        foreach (['friendly_name', 'arguments', 'type', 'value'] as $req) {
                            if (!isset($c[$req])) $errors[] = "[$n] condition sans '$req'";
                        }
                        if (!is_array($c['arguments'] ?? null)) {
                            $errors[] = "[$n] condition 'arguments' doit être array";
                        }
                    }
                }
            }
            break;
    }

    // Vérifier tous les 'next'
    foreach ($state['transitions'] as $tr) {
        if (isset($tr['next']) && !in_array($tr['next'], $allNames)) {
            $errors[] = "[$n] 'next' = '{$tr['next']}' INTROUVABLE dans les states";
        }
    }
}

// 3. Noms dupliqués
$dupes = array_filter(array_count_values($allNames), fn($c) => $c > 1);
if (!empty($dupes)) {
    $errors[] = "Noms dupliqués: " . implode(', ', array_keys($dupes));
}

// 4. Vérifier types JSON des valeurs critiques
$json = json_encode($flow);
$decoded = json_decode($json, true);

foreach ($decoded['states'] as $s) {
    if (isset($s['properties']['timeout'])) {
        $v = $s['properties']['timeout'];
        if (!is_int($v)) {
            $errors[] = "[{$s['name']}] timeout JSON type: " . gettype($v) . " (expected integer)";
        }
    }
    if (isset($s['properties']['offset'])) {
        foreach (['x', 'y'] as $k) {
            $v = $s['properties']['offset'][$k] ?? null;
            if (!is_int($v)) {
                $errors[] = "[{$s['name']}] offset.$k JSON type: " . gettype($v);
            }
        }
    }
}

// ── Résultats ──────────────────────────────────────────────────────────────
echo count($errors) === 0 ? "✅ JSON 100% COMPATIBLE TWILIO\n" : "❌ " . count($errors) . " ERREUR(S):\n";
foreach ($errors as $e) { echo "   ✗ $e\n"; }
echo "\n";

echo "── Types JSON vérifiés ─────────────────────────────────────────────\n";
foreach ($decoded['states'] as $s) {
    if (isset($s['properties']['timeout'])) {
        $v = $s['properties']['timeout'];
        echo "  [{$s['name']}] timeout = $v (" . gettype($v) . ")" . (is_int($v) ? " ✓" : " ✗") . "\n";
    }
    if (isset($s['properties']['body'])) {
        $v = $s['properties']['body'];
        $display = is_null($v) ? 'null' : (strlen($v) > 30 ? '"' . substr($v, 0, 30) . '..."' : '"' . $v . '"');
        echo "  [{$s['name']}] body = $display (" . gettype($v) . ") ✓\n";
    }
}

echo "\n── States générés ──────────────────────────────────────────────────\n";
foreach ($decoded['states'] as $s) {
    $events = implode(', ', array_column($s['transitions'], 'event'));
    echo "  " . str_pad($s['name'], 22) . " type=" . str_pad($s['type'], 26) . " events=[$events]\n";
}

// JSON final
$prettyJson = json_encode($flow, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
echo "\n── JSON final (" . strlen($prettyJson) . " chars) ─────────────────────────────────────\n";
echo $prettyJson;
