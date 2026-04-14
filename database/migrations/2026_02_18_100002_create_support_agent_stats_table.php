<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_agent_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('period')->default('day');
            $table->unsignedBigInteger('chatwoot_agent_id');
            $table->string('agent_name');
            $table->string('agent_email')->nullable();
            $table->unsignedBigInteger('conversations_count')->default(0);
            $table->unsignedBigInteger('resolutions_count')->default(0);
            $table->unsignedInteger('avg_first_response_time')->default(0); // seconds
            $table->unsignedInteger('avg_resolution_time')->default(0);     // seconds
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['date', 'period', 'chatwoot_agent_id']);
            $table->index(['date', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_agent_stats');
    }
};
