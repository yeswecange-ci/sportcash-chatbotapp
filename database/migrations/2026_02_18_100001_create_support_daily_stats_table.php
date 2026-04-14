<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('period')->default('day'); // day, week, month, quarter
            $table->unsignedBigInteger('conversations_count')->default(0);
            $table->unsignedBigInteger('resolutions_count')->default(0);
            $table->unsignedBigInteger('incoming_messages_count')->default(0);
            $table->unsignedBigInteger('outgoing_messages_count')->default(0);
            $table->unsignedInteger('avg_first_response_time')->default(0); // seconds
            $table->unsignedInteger('avg_resolution_time')->default(0);     // seconds
            $table->unsignedBigInteger('open_count')->default(0);
            $table->unsignedBigInteger('pending_count')->default(0);
            $table->unsignedBigInteger('resolved_count')->default(0);
            $table->json('trend_data')->nullable(); // [{timestamp, value}] raw from API
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['date', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_daily_stats');
    }
};
