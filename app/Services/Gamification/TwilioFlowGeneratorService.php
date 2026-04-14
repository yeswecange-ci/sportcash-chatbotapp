<?php

namespace App\Services\Gamification;

use App\Models\Game;

class TwilioFlowGeneratorService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('app.url'), '/');
    }

    /**
     * Génère le JSON complet d'un Flow Twilio Studio pour un jeu.
     * Conforme à la spec Twilio Studio v2 Flow Definition.
     */
    public function generate(Game $game): array
    {
        $slug         = $game->slug;
        $questions    = $game->questions()->orderBy('order')->get();
        $webhookBase  = "{$this->baseUrl}/gamification/webhook/{$slug}";

        $states = [];
        $x      = 0;
        $y      = 0;
        $step   = 250;

        // ── Trigger ───────────────────────────────────────────────────────────
        $states[] = [
            'name'        => 'Trigger',
            'type'        => 'trigger',
            'transitions' => [
                ['event' => 'incomingMessage',            'next' => 'check_participant'],
                ['event' => 'incomingCall'],
                ['event' => 'incomingConversationMessage','next' => 'check_participant'],
                ['event' => 'incomingRequest'],
                ['event' => 'incomingParent'],
            ],
            'properties'  => ['offset' => ['x' => $x, 'y' => $y]],
        ];

        $y += $step;

        // ── HTTP: check_participant ───────────────────────────────────────────
        $states[] = [
            'name'        => 'check_participant',
            'type'        => 'make-http-request',
            'transitions' => [
                ['event' => 'success', 'next' => 'split_eligible'],
                ['event' => 'failed',  'next' => 'send_not_eligible'],
            ],
            'properties'  => [
                'offset'       => ['x' => $x, 'y' => $y],
                'method'       => 'POST',
                'url'          => "{$webhookBase}/check",
                'body'         => null,
                'parameters'   => [
                    ['key' => 'phone', 'value' => '{{contact.channel.address}}'],
                ],
                'content_type' => 'application/x-www-form-urlencoded',
            ],
        ];

        $y += $step;

        // ── Split: eligible? ──────────────────────────────────────────────────
        $firstQ = $this->firstQuestionName($questions);

        $states[] = [
            'name'        => 'split_eligible',
            'type'        => 'split-based-on',
            'transitions' => [
                [
                    'event'      => 'match',
                    'conditions' => [
                        [
                            'friendly_name' => 'Is eligible',
                            'arguments'     => ['{{widgets.check_participant.parsed.eligible}}'],
                            'type'          => 'equal_to',
                            'value'         => 'true',
                        ],
                    ],
                    'next'       => 'split_name_known',
                ],
                ['event' => 'noMatch', 'next' => 'send_not_eligible'],
            ],
            'properties'  => [
                'offset' => ['x' => $x, 'y' => $y],
                'input'  => '{{widgets.check_participant.parsed.eligible}}',
            ],
        ];

        // ── Send: not eligible ────────────────────────────────────────────────
        $states[] = [
            'name'        => 'send_not_eligible',
            'type'        => 'send-message',
            'transitions' => [
                ['event' => 'sent'],
            ],
            'properties'  => [
                'offset' => ['x' => $x + 400, 'y' => $y],
                'from'   => '{{flow.channel.address}}',
                'to'     => '{{contact.channel.address}}',
                'body'   => "Désolé, vous n'êtes pas éligible à ce jeu ou avez déjà participé.",
            ],
        ];

        $y += $step;

        // ── Split: name_known? ────────────────────────────────────────────────
        $states[] = [
            'name'        => 'split_name_known',
            'type'        => 'split-based-on',
            'transitions' => [
                [
                    'event'      => 'match',
                    'conditions' => [
                        [
                            'friendly_name' => 'Name known',
                            'arguments'     => ['{{widgets.check_participant.parsed.name_known}}'],
                            'type'          => 'equal_to',
                            'value'         => 'true',
                        ],
                    ],
                    'next'       => $firstQ,
                ],
                ['event' => 'noMatch', 'next' => 'ask_name'],
            ],
            'properties'  => [
                'offset' => ['x' => $x, 'y' => $y],
                'input'  => '{{widgets.check_participant.parsed.name_known}}',
            ],
        ];

        $y += $step;

        // ── SendWait: ask_name ────────────────────────────────────────────────
        $states[] = [
            'name'        => 'ask_name',
            'type'        => 'send-and-wait-for-reply',
            'transitions' => [
                ['event' => 'incomingMessage', 'next' => 'save_name'],
                ['event' => 'timeout'],
                ['event' => 'delivery'],
            ],
            'properties'  => [
                'offset'  => ['x' => $x - 350, 'y' => $y],
                'from'    => '{{flow.channel.address}}',
                'to'      => '{{contact.channel.address}}',
                'body'    => "Bienvenue ! Quel est votre nom complet pour participer à \"{{widgets.check_participant.parsed.game_name}}\" ?",
                'timeout' => 3600,
            ],
        ];

        // ── HTTP: save_name ───────────────────────────────────────────────────
        $states[] = [
            'name'        => 'save_name',
            'type'        => 'make-http-request',
            'transitions' => [
                ['event' => 'success', 'next' => $firstQ],
                ['event' => 'failed'],
            ],
            'properties'  => [
                'offset'       => ['x' => $x - 350, 'y' => $y + $step],
                'method'       => 'POST',
                'url'          => "{$webhookBase}/save-name",
                'body'         => null,
                'parameters'   => [
                    ['key' => 'phone', 'value' => '{{contact.channel.address}}'],
                    ['key' => 'name',  'value' => '{{widgets.ask_name.inbound.Body}}'],
                ],
                'content_type' => 'application/x-www-form-urlencoded',
            ],
        ];

        // ── Questions ─────────────────────────────────────────────────────────
        foreach ($questions as $index => $question) {
            $qNum             = $index + 1;
            $qWidgetName      = "q{$qNum}";
            $answerWidgetName = "answer_q{$qNum}";
            $nextQ            = $questions->get($index + 1);
            $nextWidget       = $nextQ ? "q" . ($qNum + 1) : 'http_complete';

            // Corps du message de la question
            $body = $question->text;
            if (in_array($question->type, ['mcq', 'vote']) && !empty($question->options)) {
                $body .= "\n";
                foreach ($question->options as $i => $opt) {
                    $body .= "\n" . ($i + 1) . ". " . $opt;
                }
            }

            // SendWait: poser la question
            $states[] = [
                'name'        => $qWidgetName,
                'type'        => 'send-and-wait-for-reply',
                'transitions' => [
                    ['event' => 'incomingMessage', 'next' => $answerWidgetName],
                    ['event' => 'timeout'],
                    ['event' => 'delivery'],
                ],
                'properties'  => [
                    'offset'  => ['x' => $x, 'y' => $y],
                    'from'    => '{{flow.channel.address}}',
                    'to'      => '{{contact.channel.address}}',
                    'body'    => $body,
                    'timeout' => 3600,
                ],
            ];

            $y += $step;

            // HTTP: enregistrer la réponse
            $states[] = [
                'name'        => $answerWidgetName,
                'type'        => 'make-http-request',
                'transitions' => [
                    ['event' => 'success', 'next' => $nextWidget],
                    ['event' => 'failed'],
                ],
                'properties'  => [
                    'offset'       => ['x' => $x, 'y' => $y],
                    'method'       => 'POST',
                    'url'          => "{$webhookBase}/answer",
                    'body'         => null,
                    'parameters'   => [
                        ['key' => 'phone',          'value' => '{{contact.channel.address}}'],
                        ['key' => 'question_order', 'value' => (string) $qNum],
                        ['key' => 'answer',         'value' => "{{widgets.{$qWidgetName}.inbound.Body}}"],
                    ],
                    'content_type' => 'application/x-www-form-urlencoded',
                ],
            ];

            $y += $step;
        }

        // ── HTTP: complete ────────────────────────────────────────────────────
        $states[] = [
            'name'        => 'http_complete',
            'type'        => 'make-http-request',
            'transitions' => [
                ['event' => 'success', 'next' => 'send_thank_you'],
                ['event' => 'failed',  'next' => 'send_thank_you'],
            ],
            'properties'  => [
                'offset'       => ['x' => $x, 'y' => $y],
                'method'       => 'POST',
                'url'          => "{$webhookBase}/complete",
                'body'         => null,
                'parameters'   => [
                    ['key' => 'phone', 'value' => '{{contact.channel.address}}'],
                ],
                'content_type' => 'application/x-www-form-urlencoded',
            ],
        ];

        $y += $step;

        // ── Send: thank_you ───────────────────────────────────────────────────
        $thankYou = $game->thank_you_message ?: "Merci pour votre participation ! Bonne chance !";
        $states[] = [
            'name'        => 'send_thank_you',
            'type'        => 'send-message',
            'transitions' => [
                ['event' => 'sent'],
            ],
            'properties'  => [
                'offset' => ['x' => $x, 'y' => $y],
                'from'   => '{{flow.channel.address}}',
                'to'     => '{{contact.channel.address}}',
                'body'   => $thankYou,
            ],
        ];

        return [
            'description'   => "Flow Twilio Studio — " . $game->name,
            'states'        => $states,
            'initial_state' => 'Trigger',
            'flags'         => ['allow_concurrent_calls' => true],
        ];
    }

    private function firstQuestionName($questions): string
    {
        return $questions->isEmpty() ? 'http_complete' : 'q1';
    }
}
