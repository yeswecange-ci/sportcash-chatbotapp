<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportAgentStat extends Model
{
    protected $fillable = [
        'date',
        'period',
        'chatwoot_agent_id',
        'agent_name',
        'agent_email',
        'conversations_count',
        'resolutions_count',
        'avg_first_response_time',
        'avg_resolution_time',
        'synced_at',
    ];

    protected $casts = [
        'date'      => 'date',
        'synced_at' => 'datetime',
    ];

    /**
     * Upsert agent stat for a given date+period+agent.
     */
    public static function upsertForAgent(string $date, string $period, int $agentId, array $data): self
    {
        return static::updateOrCreate(
            ['date' => $date, 'period' => $period, 'chatwoot_agent_id' => $agentId],
            array_merge($data, ['synced_at' => now()])
        );
    }
}
