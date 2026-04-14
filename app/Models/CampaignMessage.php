<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignMessage extends Model
{
    protected $fillable = [
        'campaign_id',
        'contact_id',
        'twilio_message_sid',
        'template_sid',
        'status',
        'error_message',
        'sent_by',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'queued'      => 'En file',
            'sent'        => 'Envoye',
            'delivered'   => 'Delivre',
            'read'        => 'Lu',
            'failed'      => 'Echoue',
            'undelivered' => 'Non delivre',
            default       => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'queued'      => 'bg-gray-100 text-gray-600',
            'sent'        => 'bg-blue-100 text-blue-700',
            'delivered'   => 'bg-green-100 text-green-700',
            'read'        => 'bg-indigo-100 text-indigo-700',
            'failed'      => 'bg-red-100 text-red-700',
            'undelivered' => 'bg-orange-100 text-orange-700',
            default       => 'bg-gray-100 text-gray-600',
        };
    }
}
