<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kash_bot_messages', function (Blueprint $table) {
            $table->id();
            $table->string('sender', 60)->index();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->text('content');
            $table->string('signal_type', 20)->default('none'); // none|reclamation|escalade
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kash_bot_messages');
    }
};
