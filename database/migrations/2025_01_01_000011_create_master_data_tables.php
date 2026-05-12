<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lembaga_akreditasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lembaga');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('auditee_pusat', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('auditee', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama_auditee');
            $table->string('jenjang');
            $table->foreignId('auditee_pusat_id')->constrained('auditee_pusat')->cascadeOnDelete();
            $table->text('alamat')->nullable();
            $table->string('akreditasi')->nullable()->comment('A, B, C, Baik, Baik Sekali, Unggul');
            $table->string('sk_no')->nullable()->comment('Nomor SK');
            $table->date('sk_tanggal')->nullable()->comment('Tanggal SK');
            $table->string('sk_file_path')->nullable()->comment('PDF file path SK');
            $table->timestamps();
        });

        Schema::create('unit_penunjang', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama_unit');
            $table->foreignId('auditee_pusat_id')->constrained('auditee_pusat')->cascadeOnDelete();
            $table->string('jenjang')->nullable();
            $table->text('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_penunjang');
        Schema::dropIfExists('auditee');
        Schema::dropIfExists('auditee_pusat');
        Schema::dropIfExists('lembaga_akreditasi');
    }
};
