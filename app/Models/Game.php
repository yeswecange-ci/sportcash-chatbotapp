<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Game extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'status',
        'eligibility',
        'start_date',
        'end_date',
        'max_participants',
        'thank_you_message',
        'synced_at',
    ];

    protected $casts = [
        'status'     => 'string',
        'eligibility' => 'string',
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'synced_at'  => 'datetime',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(GameQuestion::class)->orderBy('order');
    }

    public function participations(): HasMany
    {
        return $this->hasMany(GameParticipation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public static function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    public function participantsCount(): int
    {
        return $this->participations()->count();
    }

    public function completedCount(): int
    {
        return $this->participations()->where('status', 'completed')->count();
    }

    public function isEnded(): bool
    {
        return $this->end_date !== null && $this->end_date->isPast();
    }
}
