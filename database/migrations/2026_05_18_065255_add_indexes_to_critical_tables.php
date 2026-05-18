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
        Schema::table('users', function (Blueprint $table) {
            $table->index('is_active');
        });

        Schema::table('manajemen_dokumen', function (Blueprint $table) {
            $table->index('tahun');
        });

        Schema::table('tahun_periode', function (Blueprint $table) {
            $table->index('status');
            $table->index('tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahun_periode', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['tahun']);
        });

        Schema::table('manajemen_dokumen', function (Blueprint $table) {
            $table->dropIndex(['tahun']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });
    }
};
