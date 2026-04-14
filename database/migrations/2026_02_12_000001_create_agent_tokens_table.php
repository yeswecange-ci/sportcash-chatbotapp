<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('chatwoot_agent_id')->nullable();
            $table->string('chatwoot_access_token');
            $table->string('chatwoot_agent_name')->nullable();
            $table->string('chatwoot_agent_email')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('chatwoot_agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_tokens');
    }
};
