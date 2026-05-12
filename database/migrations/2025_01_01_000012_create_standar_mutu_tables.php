<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standar_mutu', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->comment('A, A.1, A.1.1, dst');
            $table->string('nama_standar');
            $table->foreignId('parent_id')->nullable()->constrained('standar_mutu')->nullOnDelete();
            $table->foreignId('lembaga_akreditasi_id')->constrained('lembaga_akreditasi')->cascadeOnDelete();
            $table->foreignId('tahun_periode_id')->constrained('tahun_periode')->cascadeOnDelete();
            $table->unsignedTinyInteger('level')->comment('1=Standar, 2=Sub Standar, 3=Butir');
            $table->text('data_dukung')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standar_mutu_id')->constrained('standar_mutu')->cascadeOnDelete();
            $table->text('deskripsi');
            $table->unsignedInteger('bobot')->nullable()->comment('Bobot penilaian indikator');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indikator');
        Schema::dropIfExists('standar_mutu');
    }
};
