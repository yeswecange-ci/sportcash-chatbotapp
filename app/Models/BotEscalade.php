<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotEscalade extends Model
{
    protected $table = 'bot_escalades';

    protected $fillable = [
        'reference',
        'identifiant',
        'sender',
        'raison',
        'resume',
        'statut',
        'assigned_to',
        'chatwoot_conversation_id',
        'notes',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // ── Statuts ──────────────────────────────────────────────────────────────

    public static function statuts(): array
    {
        return ['en_attente', 'en_cours', 'resolue', 'fermee'];
    }

    public function statutLabel(): string
    {
        return match ($this->statut) {
            'en_attente' => 'En attente',
            'en_cours'   => 'En cours',
            'resolue'    => 'Résolue',
            'fermee'     => 'Fermée',
            default      => $this->statut,
        };
    }

    public function statutBadgeClass(): string
    {
        return match ($this->statut) {
            'en_attente' => 'bg-amber-100 text-amber-700',
            'en_cours'   => 'bg-blue-100 text-blue-700',
            'resolue'    => 'bg-green-100 text-green-700',
            'fermee'     => 'bg-gray-100 text-gray-500',
            default      => 'bg-gray-100 text-gray-500',
        };
    }
}
