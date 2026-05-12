<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daftar_temuan_pp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('temuan_id')->constrained('temuan')->cascadeOnDelete();
            $table->foreignId('rekap_approval_id')->constrained('rekap_temuan_approval')->cascadeOnDelete();
            $table->foreignId('auditee_id')->constrained('auditee')->cascadeOnDelete();
            $table->foreignId('pengaturan_periode_id')->constrained('pengaturan_periode')->cascadeOnDelete();
            $table->text('uraian_temuan')->nullable();
            $table->string('jenis')->nullable()->comment('KTS Mayor, KTS Minor, Observasi');
            $table->enum('status', ['Open', 'In Progress', 'Closed'])->default('Open');
            $table->timestamps();
        });

        Schema::create('daftar_kesesuaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditee_id')->constrained('auditee')->cascadeOnDelete();
            $table->foreignId('standar_mutu_id')->constrained('standar_mutu')->cascadeOnDelete();
            $table->foreignId('pengaturan_periode_id')->constrained('pengaturan_periode')->cascadeOnDelete();
            $table->text('deskripsi')->nullable();
            $table->text('peningkatan')->nullable()->comment('Uraian peningkatan yang dilakukan');
            $table->float('nilai_mutu')->nullable();
            $table->text('temuan_positif')->nullable();
            $table->string('filter_tampilan')->nullable()->comment('Semua / Per Program Studi');
            $table->timestamps();
        });

        Schema::create('rencana_tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daftar_temuan_pp_id')->constrained('daftar_temuan_pp')->cascadeOnDelete();
            $table->foreignId('auditee_id')->constrained('auditee')->cascadeOnDelete();
            $table->text('uraian_rtm');
            $table->string('penanggung_jawab')->nullable();
            $table->date('target_selesai')->nullable();
            $table->enum('status', ['Belum', 'Dalam Proses', 'Selesai'])->default('Belum');
            $table->timestamps();
        });

        Schema::create('draft_laporan_rtm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rencana_tindak_lanjut_id')->constrained('rencana_tindak_lanjut')->cascadeOnDelete();
            $table->foreignId('pengaturan_periode_id')->constrained('pengaturan_periode')->cascadeOnDelete();
            $table->foreignId('auditee_id')->constrained('auditee')->cascadeOnDelete();
            $table->string('nama_dokumen')->comment('LAPORAN RTM');
            $table->string('file_path')->nullable();
            $table->date('tanggal_dibuat')->nullable();
            $table->enum('status', ['Draft', 'Final'])->default('Draft');
            $table->timestamps();
        });

        Schema::create('upload_laporan_rtm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draft_laporan_rtm_id')->constrained('draft_laporan_rtm')->cascadeOnDelete();
            $table->foreignId('auditee_id')->constrained('auditee')->cascadeOnDelete();
            $table->foreignId('tahun_periode_id')->constrained('tahun_periode')->cascadeOnDelete();
            $table->string('nama_dokumen');
            $table->string('file_path');
            $table->dateTime('tanggal_upload')->nullable();
            $table->enum('status_download', ['Tersedia', 'Tidak Tersedia'])->default('Tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_laporan_rtm');
        Schema::dropIfExists('draft_laporan_rtm');
        Schema::dropIfExists('rencana_tindak_lanjut');
        Schema::dropIfExists('daftar_kesesuaian');
        Schema::dropIfExists('daftar_temuan_pp');
    }
};
