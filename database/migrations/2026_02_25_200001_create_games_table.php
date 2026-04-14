<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['quiz', 'free_text', 'vote', 'prediction']);
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->enum('eligibility', ['all', 'clients_only'])->default('all');
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->integer('max_participants')->nullable();
            $table->text('thank_you_message')->nullable();
            $table->datetime('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
