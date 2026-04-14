<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentToken extends Model
{
    protected $fillable = [
        'user_id',
        'chatwoot_agent_id',
        'chatwoot_access_token',
        'chatwoot_agent_name',
        'chatwoot_agent_email',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
