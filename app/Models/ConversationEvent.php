<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'event_type',
        'widget_name',
        'widget_type',
        'user_input',
        'expected_input_type',
        'bot_message',
        'media_url',
        'menu_name',
        'choice_label',
        'menu_path',
        'metadata',
        'response_time_ms',
        'event_at',
    ];

    protected $casts = [
        'menu_path' => 'array',
        'metadata' => 'array',
        'event_at' => 'datetime',
    ];

    /**
     * Relation avec la conversation
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Vérifier si c'est une saisie libre
     */
    public function isFreeInput(): bool
    {
        return $this->event_type === 'free_input';
    }

    /**
     * Vérifier si c'est un choix de menu
     */
    public function isMenuChoice(): bool
    {
        return $this->event_type === 'menu_choice';
    }

    /**
     * Vérifier si c'est un transfert agent
     */
    public function isAgentTransfer(): bool
    {
        return $this->event_type === 'agent_transfer';
    }

    /**
     * Scope pour les saisies libres
     */
    public function scopeFreeInputs($query)
    {
        return $query->where('event_type', 'free_input');
    }

    /**
     * Scope pour les choix de menu
     */
    public function scopeMenuChoices($query)
    {
        return $query->where('event_type', 'menu_choice');
    }

    /**
     * Scope pour les transferts
     */
    public function scopeTransfers($query)
    {
        return $query->where('event_type', 'agent_transfer');
    }

    /**
     * Scope pour les erreurs
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('event_type', ['error', 'invalid_input']);
    }

    /**
     * Créer un événement de saisie libre
     */
    public static function logFreeInput(
        Conversation $conversation,
        string $widgetName,
        string $userInput,
        ?string $inputType = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'conversation_id' => $conversation->id,
            'event_type' => 'free_input',
            'widget_name' => $widgetName,
            'user_input' => $userInput,
            'expected_input_type' => $inputType,
            'metadata' => $metadata,
            'event_at' => now(),
        ]);
    }

    /**
     * Créer un événement de choix de menu
     */
    public static function logMenuChoice(
        Conversation $conversation,
        string $widgetName,
        string $userInput,
        string $menuName,
        ?string $choiceLabel = null,
        ?array $menuPath = null
    ): self {
        return self::create([
            'conversation_id' => $conversation->id,
            'event_type' => 'menu_choice',
            'widget_name' => $widgetName,
            'user_input' => $userInput,
            'menu_name' => $menuName,
            'choice_label' => $choiceLabel,
            'menu_path' => $menuPath,
            'event_at' => now(),
        ]);
    }

    /**
     * Créer un événement de message envoyé
     */
    public static function logMessageSent(
        Conversation $conversation,
        string $widgetName,
        string $botMessage,
        ?string $mediaUrl = null
    ): self {
        return self::create([
            'conversation_id' => $conversation->id,
            'event_type' => $mediaUrl ? 'document_sent' : 'message_sent',
            'widget_name' => $widgetName,
            'bot_message' => $botMessage,
            'media_url' => $mediaUrl,
            'event_at' => now(),
        ]);
    }

    /**
     * Créer un événement de transfert agent
     */
    public static function logAgentTransfer(
        Conversation $conversation,
        string $widgetName,
        ?string $reason = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'conversation_id' => $conversation->id,
            'event_type' => 'agent_transfer',
            'widget_name' => $widgetName,
            'choice_label' => $reason,
            'metadata' => $metadata,
            'event_at' => now(),
        ]);
    }
}
