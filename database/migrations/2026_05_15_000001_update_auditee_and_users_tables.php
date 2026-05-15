<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add sk_tanggal_selesai (end date for accreditation) and keterangan to auditee
        if (!Schema::hasColumn('auditee', 'sk_tanggal_selesai')) {
            Schema::table('auditee', function (Blueprint $table) {
                $table->date('sk_tanggal_selesai')->nullable()->after('sk_tanggal')->comment('Tanggal selesai akreditasi');
            });
        }
        if (!Schema::hasColumn('auditee', 'keterangan')) {
            Schema::table('auditee', function (Blueprint $table) {
                $table->text('keterangan')->nullable()->after('sk_file_path');
            });
        }

        // Add keterangan, is_active, and entity linking to users
        if (!Schema::hasColumn('users', 'keterangan')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('keterangan')->nullable()->after('email');
            });
        }
        if (!Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('keterangan');
            });
        }
        if (!Schema::hasColumn('users', 'auditee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('auditee_id')->nullable()->after('is_active')
                      ->constrained('auditee')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('users', 'auditor_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('auditor_id')->nullable()->after('auditee_id')
                      ->constrained('auditor')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('users', 'auditee_pusat_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('auditee_pusat_id')->nullable()->after('auditor_id')
                      ->constrained('auditee_pusat')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('users', 'unit_penunjang_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('unit_penunjang_id')->nullable()->after('auditee_pusat_id')
                      ->constrained('unit_penunjang')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['auditee_id', 'auditor_id', 'auditee_pusat_id', 'unit_penunjang_id'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropForeign([$col]);
                }
            }
            $cols = array_filter(['keterangan', 'is_active', 'auditee_id', 'auditor_id', 'auditee_pusat_id', 'unit_penunjang_id'], fn($c) => Schema::hasColumn('users', $c));
            if ($cols) $table->dropColumn($cols);
        });

        Schema::table('auditee', function (Blueprint $table) {
            $cols = array_filter(['sk_tanggal_selesai', 'keterangan'], fn($c) => Schema::hasColumn('auditee', $c));
            if ($cols) $table->dropColumn($cols);
        });
    }
};
