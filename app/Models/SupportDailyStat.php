<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportDailyStat extends Model
{
    protected $fillable = [
        'date',
        'period',
        'conversations_count',
        'resolutions_count',
        'incoming_messages_count',
        'outgoing_messages_count',
        'avg_first_response_time',
        'avg_resolution_time',
        'open_count',
        'pending_count',
        'resolved_count',
        'trend_data',
        'synced_at',
    ];

    protected $casts = [
        'date'       => 'date',
        'trend_data' => 'array',
        'synced_at'  => 'datetime',
    ];

    /**
     * Upsert a stat record for a given date+period.
     */
    public static function upsertForPeriod(string $date, string $period, array $data): self
    {
        return static::updateOrCreate(
            ['date' => $date, 'period' => $period],
            array_merge($data, ['synced_at' => now()])
        );
    }

    /**
     * Last sync time across all records.
     */
    public static function lastSyncedAt(): ?\Illuminate\Support\Carbon
    {
        $row = static::orderByDesc('synced_at')->first();
        return $row?->synced_at;
    }
}
