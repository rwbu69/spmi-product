<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_periode_id')->constrained('tahun_periode')->cascadeOnDelete();
            $table->foreignId('lembaga_akreditasi_id')->constrained('lembaga_akreditasi')->cascadeOnDelete();
            $table->date('mulai_evaluasi_diri')->nullable();
            $table->date('akhir_evaluasi_diri')->nullable();
            $table->date('mulai_desk_eval')->nullable();
            $table->date('akhir_desk_eval')->nullable();
            $table->date('mulai_visitasi')->nullable();
            $table->date('akhir_visitasi')->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Tidak Aktif');
            $table->timestamps();
        });

        Schema::create('target_nilai_mutu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaturan_periode_id')->constrained('pengaturan_periode')->cascadeOnDelete();
            $table->foreignId('auditee_id')->constrained('auditee')->cascadeOnDelete();
            $table->float('target_nilai');
            $table->timestamps();

            $table->unique(['pengaturan_periode_id', 'auditee_id']);
        });

        Schema::create('evaluasi_diri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditee_id')->constrained('auditee')->cascadeOnDelete();
            $table->foreignId('pengaturan_periode_id')->constrained('pengaturan_periode')->cascadeOnDelete();
            $table->float('nilai_evaluasi')->default(0.00);
            $table->enum('status', ['Draft', 'Submitted', 'Approved'])->default('Draft');
            $table->timestamps();
        });

        Schema::create('nilai_mutu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditee_id')->constrained('auditee')->cascadeOnDelete();
            $table->foreignId('pengaturan_periode_id')->constrained('pengaturan_periode')->cascadeOnDelete();
            $table->foreignId('lembaga_akreditasi_id')->constrained('lembaga_akreditasi')->cascadeOnDelete();
            $table->float('nilai')->default(0.00);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_mutu');
        Schema::dropIfExists('evaluasi_diri');
        Schema::dropIfExists('target_nilai_mutu');
        Schema::dropIfExists('pengaturan_periode');
    }
};
