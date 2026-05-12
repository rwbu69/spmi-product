<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori')->unique();
            $table->timestamps();
        });

        Schema::create('jenis_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jenis');
            $table->foreignId('kategori_dokumen_id')->constrained('kategori_dokumen')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('manajemen_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_dokumen_id')->constrained('jenis_dokumen')->cascadeOnDelete();
            $table->foreignId('auditee_id')->constrained('auditee')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_dokumen');
            $table->integer('tahun')->nullable();
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manajemen_dokumen');
        Schema::dropIfExists('jenis_dokumen');
        Schema::dropIfExists('kategori_dokumen');
    }
};
