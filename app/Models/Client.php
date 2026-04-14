<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'phone_number',
        'whatsapp_profile_name',
        'client_full_name',
        'email',
        'is_client',
        'vin',
        'carte_vip',
        'interaction_count',
        'conversation_count',
        'first_interaction_at',
        'last_interaction_at',
    ];

    protected $casts = [
        'is_client' => 'boolean',
        'interaction_count' => 'integer',
        'conversation_count' => 'integer',
        'first_interaction_at' => 'datetime',
        'last_interaction_at' => 'datetime',
    ];

    /**
     * Get all conversations for this client
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'phone_number', 'phone_number');
    }

    /**
     * Find or create a client by phone number
     */
    public static function findOrCreateByPhone(string $phoneNumber): self
    {
        return self::firstOrCreate(
            ['phone_number' => $phoneNumber],
            [
                'interaction_count' => 0,
                'conversation_count' => 0,
                'first_interaction_at' => now(),
                'last_interaction_at' => now(),
            ]
        );
    }

    /**
     * Update client information from a conversation
     */
    public function updateFromConversation(Conversation $conversation): void
    {
        $updates = [];

        // Mise à jour du profil WhatsApp (toujours à jour)
        if ($conversation->whatsapp_profile_name) {
            $updates['whatsapp_profile_name'] = $conversation->whatsapp_profile_name;
        }

        // Mise à jour du nom complet (uniquement si pas déjà renseigné)
        if ($conversation->client_full_name && !$this->client_full_name) {
            $updates['client_full_name'] = $conversation->client_full_name;
        }

        if ($conversation->email && !$this->email) {
            $updates['email'] = $conversation->email;
        }

        if ($conversation->is_client !== null && $this->is_client === null) {
            $updates['is_client'] = $conversation->is_client;
        }

        if ($conversation->vin && !$this->vin) {
            $updates['vin'] = $conversation->vin;
        }

        if ($conversation->carte_vip && !$this->carte_vip) {
            $updates['carte_vip'] = $conversation->carte_vip;
        }

        if (!empty($updates)) {
            $this->update($updates);
        }
    }

    /**
     * Recalculate and update last_interaction_at from conversations
     */
    public function updateLastInteractionAt(): void
    {
        $lastConversation = Conversation::where('phone_number', $this->phone_number)
            ->orderBy('last_activity_at', 'desc')
            ->orderBy('started_at', 'desc')
            ->first();

        if ($lastConversation) {
            $this->update([
                'last_interaction_at' => $lastConversation->last_activity_at ?? $lastConversation->started_at
            ]);
        }
    }

    /**
     * Increment interaction count
     */
    public function incrementInteractions(int $count = 1): void
    {
        $this->increment('interaction_count', $count);
        $this->update(['last_interaction_at' => now()]);
    }

    /**
     * Increment conversation count
     */
    public function incrementConversations(): void
    {
        $this->increment('conversation_count');
    }

    /**
     * Scope to get clients only (not non-clients)
     */
    public function scopeIsClient($query)
    {
        return $query->where('is_client', true);
    }

    /**
     * Scope to get non-clients
     */
    public function scopeIsNotClient($query)
    {
        return $query->where('is_client', false);
    }

    /**
     * Scope to get recent clients
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('last_interaction_at', '>=', now()->subDays($days));
    }

    /**
     * Get the display name for the client
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

    /**
     * Get total interaction duration in seconds
     */
    public function getTotalDurationAttribute(): int
    {
        return $this->conversations()
            ->whereNotNull('duration_seconds')
            ->sum('duration_seconds');
    }

    /**
     * Get all conversation events for this client
     */
    public function getAllEvents()
    {
        return ConversationEvent::whereIn(
            'conversation_id',
            $this->conversations()->pluck('id')
        )->orderBy('event_at', 'desc')->get();
    }
}
