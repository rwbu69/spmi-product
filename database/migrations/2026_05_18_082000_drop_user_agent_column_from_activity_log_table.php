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
        Schema::table('activity_log', function (Blueprint $table) {
            if (Schema::hasColumn('activity_log', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
            if (Schema::hasColumn('activity_log', 'browser')) {
                $table->dropColumn('browser');
            }
            if (Schema::hasColumn('activity_log', 'device')) {
                $table->dropColumn('device');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
