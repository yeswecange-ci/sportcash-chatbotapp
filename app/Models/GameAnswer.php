<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAnswer extends Model
{
    protected $fillable = [
        'participation_id',
        'question_id',
        'answer_text',
        'answered_at',
        'is_correct',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'is_correct'  => 'boolean',
    ];

    public function participation(): BelongsTo
    {
        return $this->belongsTo(GameParticipation::class, 'participation_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(GameQuestion::class, 'question_id');
    }
}
