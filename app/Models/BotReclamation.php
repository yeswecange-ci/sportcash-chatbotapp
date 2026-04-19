<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotReclamation extends Model
{
    protected $table = 'bot_reclamations';

    protected $fillable = [
        'reference',
        'identifiant',
        'sender',
        'canal',
        'type_reclamation',
        'infos',
        'priorite',
        'statut',
        'assigned_to',
        'notes',
        'chatwoot_conversation_id',
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

    // ── Priorité ─────────────────────────────────────────────────────────────

    public function prioriteBadgeClass(): string
    {
        return $this->priorite === 'haute'
            ? 'bg-red-100 text-red-700'
            : 'bg-gray-100 text-gray-500';
    }

    public function prioriteLabel(): string
    {
        return $this->priorite === 'haute' ? 'Haute' : 'Normale';
    }

    // ── Canal ────────────────────────────────────────────────────────────────

    public function canalLabel(): string
    {
        return match ($this->canal) {
            'application'   => 'Application',
            'site_web'      => 'Site web',
            'ussd'          => 'USSD',
            'point_de_vente' => 'Point de vente',
            default         => $this->canal ?? '—',
        };
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeHaute($query)
    {
        return $query->where('priorite', 'haute');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }
}
