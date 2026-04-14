<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'agent_id')) {
                $table->unsignedBigInteger('agent_id')->nullable()->after('status');
                $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('conversations', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('started_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn(['agent_id', 'ended_at']);
        });
    }
};
