<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KashBotMessage extends Model
{
    protected $table = 'kash_bot_messages';

    protected $fillable = ['sender', 'direction', 'content', 'signal_type'];

    public function phoneLabel(): string
    {
        return str_replace('whatsapp:', '', $this->sender);
    }
}
