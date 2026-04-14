<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameParticipation extends Model
{
    protected $fillable = [
        'game_id',
        'phone_number',
        'participant_name',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(GameAnswer::class, 'participation_id');
    }

    public function complete(): void
    {
        $this->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
    }
}
