<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daftar_temuan_pp', function (Blueprint $table) {
            $table->unsignedBigInteger('temuan_id')->nullable()->change();
            $table->unsignedBigInteger('rekap_approval_id')->nullable()->change();
        });

        Schema::table('draft_laporan_rtm', function (Blueprint $table) {
            $table->unsignedBigInteger('rencana_tindak_lanjut_id')->nullable()->change();
        });

        Schema::table('upload_laporan_rtm', function (Blueprint $table) {
            $table->unsignedBigInteger('draft_laporan_rtm_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Not necessary to revert
    }
};
