<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameQuestion extends Model
{
    protected $fillable = [
        'game_id',
        'order',
        'text',
        'type',
        'options',
        'correct_answer',
    ];

    protected $casts = [
        'options' => 'array',
        'order'   => 'integer',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(GameAnswer::class, 'question_id');
    }

    /**
     * Vérifie si la réponse donnée est correcte.
     * Retourne null si pas de bonne réponse définie.
     */
    public function checkAnswer(string $answer): ?bool
    {
        if (empty($this->correct_answer)) {
            return null;
        }

        $given   = mb_strtolower(trim($answer));
        $correct = mb_strtolower(trim($this->correct_answer));

        // Comparaison directe
        if ($given === $correct) {
            return true;
        }

        // Pour MCQ/vote : l'utilisateur peut avoir tapé "1", "2", etc. (index 1-based)
        if (in_array($this->type, ['mcq', 'vote']) && is_array($this->options)) {
            $index = (int) $given - 1;
            if ($index >= 0 && isset($this->options[$index])) {
                return mb_strtolower(trim($this->options[$index])) === $correct;
            }
        }

        return false;
    }
}
