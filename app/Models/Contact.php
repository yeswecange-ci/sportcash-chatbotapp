<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'created_by',
        'chatwoot_contact_id',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class)->withTimestamps();
    }

    public function campaignMessages(): HasMany
    {
        return $this->hasMany(CampaignMessage::class);
    }

    /**
     * Normalize phone to E.164 format
     */
    public function setPhoneNumberAttribute(string $value): void
    {
        $clean = preg_replace('/[\s\-\(\)]/', '', $value);
        if (!str_starts_with($clean, '+')) {
            $clean = '+' . $clean;
        }
        $this->attributes['phone_number'] = $clean;
    }

    public function initials(): string
    {
        $parts = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $initials ?: '?';
    }
}
