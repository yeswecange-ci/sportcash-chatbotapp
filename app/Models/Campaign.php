<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'template_sid',
        'template_name',
        'template_body',
        'template_variables',
        'scheduled_at',
        'sent_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'template_variables' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class)->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CampaignMessage::class);
    }

    public function hasPendingSchedule(): bool
    {
        return $this->status === 'scheduled'
            && $this->scheduled_at !== null
            && $this->scheduled_at->isFuture();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'     => 'Brouillon',
            'scheduled' => 'Planifiee',
            'active'    => 'Active',
            'completed' => 'Terminee',
            'paused'    => 'En pause',
            default     => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'draft'     => 'bg-gray-100 text-gray-600',
            'scheduled' => 'bg-purple-100 text-purple-700',
            'active'    => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'paused'    => 'bg-yellow-100 text-yellow-700',
            default     => 'bg-gray-100 text-gray-600',
        };
    }
}
