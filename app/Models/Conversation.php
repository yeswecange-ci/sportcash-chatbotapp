<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'phone_number',
        'whatsapp_profile_name',
        'client_full_name',
        'is_client',
        'email',
        'vin',
        'carte_vip',
        'chatwoot_conversation_id',
        'chatwoot_contact_id',
        'status',
        'current_menu',
        'menu_path',
        'last_widget',
        'started_at',
        'ended_at',
        'last_activity_at',
        'transferred_at',
        'agent_id',
        'duration_seconds',
    ];

    protected $casts = [
        'is_client' => 'boolean',
        'menu_path' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'transferred_at' => 'datetime',
    ];

    /**
     * Relation avec les événements
     */
    public function events(): HasMany
    {
        return $this->hasMany(ConversationEvent::class)->orderBy('event_at');
    }

    /**
     * Relation avec l'agent (User)
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Récupérer les événements de saisie libre
     */
    public function freeInputEvents(): HasMany
    {
        return $this->hasMany(ConversationEvent::class)
                    ->where('event_type', 'free_input')
                    ->orderBy('event_at');
    }

    /**
     * Récupérer le parcours complet
     * Retourne directement menu_path (maintenu en temps réel par les webhooks)
     */
    public function getFullPathAttribute(): array
    {
        return $this->menu_path ?? [];
    }

    /**
     * Vérifier si la conversation est active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Vérifier si transférée à un agent
     */
    public function isTransferred(): bool
    {
        return $this->status === 'transferred';
    }

    /**
     * Scope pour les conversations actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour les conversations d'aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    /**
     * Scope pour les conversations transférées
     */
    public function scopeTransferred($query)
    {
        return $query->where('status', 'transferred');
    }

    /**
     * Trouver ou créer par session_id
     */
    public static function findOrCreateBySession(string $sessionId, string $phoneNumber): self
    {
        return self::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'phone_number' => $phoneNumber,
                'status' => 'active',
                'started_at' => now(),
                'last_activity_at' => now(),
            ]
        );
    }

    /**
     * Mettre à jour l'activité
     */
    public function updateActivity(): bool
    {
        $this->last_activity_at = now();
        return $this->save();
    }

    /**
     * Terminer la conversation
     */
    public function complete(): bool
    {
        $this->status = 'completed';
        $this->ended_at = now();
        if ($this->started_at) {
            $this->duration_seconds = $this->started_at->diffInSeconds($this->ended_at);
        }
        return $this->save();
    }

    /**
     * Marquer comme transférée
     */
    public function markAsTransferred(int $chatwootConversationId): bool
    {
        $this->status = 'transferred';
        $this->chatwoot_conversation_id = $chatwootConversationId;
        $this->transferred_at = now();
        return $this->save();
    }

    /**
     * Get the display name for the conversation
     * Returns client_full_name if available, otherwise whatsapp_profile_name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->client_full_name ?? $this->whatsapp_profile_name ?? 'Client inconnu';
    }

    /**
     * Check if client has provided their full name
     */
    public function hasFullName(): bool
    {
        return !empty($this->client_full_name);
    }
}
