<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manajemen_dokumen', function (Blueprint $table) {
            // Make auditee_id nullable so documents can belong to either auditee or unit_penunjang
            $table->unsignedBigInteger('auditee_id')->nullable()->change();
            // Add optional link to unit_penunjang
            $table->foreignId('unit_penunjang_id')->nullable()->constrained('unit_penunjang')->nullOnDelete()->after('auditee_id');
        });
    }

    public function down(): void
    {
        Schema::table('manajemen_dokumen', function (Blueprint $table) {
            $table->dropForeign(['unit_penunjang_id']);
            $table->dropColumn('unit_penunjang_id');
            $table->unsignedBigInteger('auditee_id')->nullable(false)->change();
        });
    }
};
