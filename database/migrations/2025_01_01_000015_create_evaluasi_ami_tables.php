<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditor', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email')->unique()->nullable();
            $table->string('keahlian')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('jenis_temuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('status', ['Positif', 'Negatif']);
            $table->timestamps();
        });

        Schema::create('kategori_temuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori')->comment('Mayor, Minor, Observasi, Tidak Melampaui, Melampaui');
            $table->foreignId('jenis_temuan_id')->constrained('jenis_temuan')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('desk_evaluation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluasi_diri_id')->constrained('evaluasi_diri')->cascadeOnDelete();
            $table->foreignId('auditor_id')->constrained('auditor')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('indikator')->cascadeOnDelete();
            $table->float('nilai')->default(0.00);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('temuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desk_evaluation_id')->constrained('desk_evaluation')->cascadeOnDelete();
            $table->foreignId('kategori_temuan_id')->constrained('kategori_temuan')->cascadeOnDelete();
            $table->text('deskripsi');
            $table->text('rekomendasi')->nullable();
            $table->enum('status_approval', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->timestamps();
        });

        Schema::create('rekap_temuan_approval', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditee_id')->constrained('auditee')->cascadeOnDelete();
            $table->foreignId('pengaturan_periode_id')->constrained('pengaturan_periode')->cascadeOnDelete();
            $table->integer('jumlah_temuan')->default(0);
            $table->enum('status_approval', ['Pending', 'Approved'])->default('Pending');
            $table->date('tanggal_approval')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
        });

        Schema::create('laporan_ami', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaturan_periode_id')->constrained('pengaturan_periode')->cascadeOnDelete();
            $table->foreignId('auditee_id')->constrained('auditee')->cascadeOnDelete();
            $table->string('file_laporan')->nullable();
            $table->date('tanggal_laporan')->nullable();
            $table->enum('status', ['Draft', 'Final'])->default('Draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_ami');
        Schema::dropIfExists('rekap_temuan_approval');
        Schema::dropIfExists('temuan');
        Schema::dropIfExists('desk_evaluation');
        Schema::dropIfExists('kategori_temuan');
        Schema::dropIfExists('jenis_temuan');
        Schema::dropIfExists('auditor');
    }
};
