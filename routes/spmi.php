<?php

use App\Http\Controllers\Ami\AuditorController;
use App\Http\Controllers\Ami\JenisTemuanController;
use App\Http\Controllers\Ami\KategoriTemuanController;
use App\Http\Controllers\Ami\LaporanAmiController;
use App\Http\Controllers\Ami\RekapDeskEvalController;
use App\Http\Controllers\Ami\TemuanKolektifController;
use App\Http\Controllers\Dokumen\JenisDokumenController;
use App\Http\Controllers\Dokumen\KategoriDokumenController;
use App\Http\Controllers\Dokumen\ManajemenDokumenController;
use App\Http\Controllers\Pengendalian\DaftarKesesuaianController;
use App\Http\Controllers\Pengendalian\DaftarTemuanController;
use App\Http\Controllers\Pengendalian\DraftRtmController;
use App\Http\Controllers\Pengendalian\UploadLaporanRtmController;
use App\Http\Controllers\Pelaksanaan\EvaluasiDiriController;
use App\Http\Controllers\Pelaksanaan\PengaturanPeriodeController;
use App\Http\Controllers\Pelaksanaan\TargetNilaiMutuController;
use App\Http\Controllers\Penetapan\NilaiMutuController;
use App\Http\Controllers\Penetapan\StandarMutuController;
use App\Http\Controllers\Penetapan\IndikatorController;
use App\Http\Controllers\Referensi\AuditeeController;
use App\Http\Controllers\Referensi\AuditeePusatController;
use App\Http\Controllers\Referensi\LembagaAkreditasiController;
use App\Http\Controllers\Referensi\TahunPeriodeController;
use App\Http\Controllers\Referensi\UnitPenunjangController;
use App\Http\Controllers\Pengaturan\PenggunaPortalController;
use App\Http\Controllers\Pengaturan\PenggunaBackofficeController;
use App\Http\Controllers\BerandaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    // Beranda / Dashboard
    Route::get('/dashboard', [BerandaController::class, 'index'])->name('dashboard');

    // ── Manajemen Referensi ────────────────────────────────
    Route::prefix('referensi')->name('referensi.')->group(function () {
        Route::resource('tahun-periode', TahunPeriodeController::class)->except(['create', 'edit', 'show']);
        Route::resource('lembaga-akreditasi', LembagaAkreditasiController::class)->except(['create', 'edit', 'show']);
        Route::resource('auditee-pusat', AuditeePusatController::class)->except(['create', 'edit', 'show']);
        Route::resource('auditee', AuditeeController::class)->except(['create', 'edit', 'show']);
        Route::resource('unit-penunjang', UnitPenunjangController::class)->except(['create', 'edit', 'show']);
    });

    // ── Manajemen Dokumen ──────────────────────────────────
    Route::prefix('dokumen')->name('dokumen.')->group(function () {
        Route::resource('kategori', KategoriDokumenController::class)->except(['create', 'edit', 'show']);
        Route::resource('jenis', JenisDokumenController::class)->except(['create', 'edit', 'show']);
        Route::resource('manajemen', ManajemenDokumenController::class)->except(['create', 'edit', 'show']);
    });

    // ── Penetapan ──────────────────────────────────────────
    Route::prefix('penetapan')->name('penetapan.')->group(function () {
        Route::resource('nilai-mutu', NilaiMutuController::class)->except(['create', 'edit', 'show']);
        Route::resource('standar-mutu', StandarMutuController::class)->except(['create', 'edit']);
        Route::resource('indikator', IndikatorController::class)->only(['store', 'update', 'destroy']);
    });

    // ── Pelaksanaan ────────────────────────────────────────
    Route::prefix('pelaksanaan')->name('pelaksanaan.')->group(function () {
        Route::resource('pengaturan-periode', PengaturanPeriodeController::class)->except(['create', 'edit', 'show']);
        Route::resource('target-nilai', TargetNilaiMutuController::class)->except(['create', 'edit', 'show']);
        Route::resource('evaluasi-diri', EvaluasiDiriController::class)->only(['index', 'store', 'destroy']);
    });

    // ── Evaluasi AMI ───────────────────────────────────────
    Route::prefix('ami')->name('ami.')->group(function () {
        Route::resource('auditor', AuditorController::class)->except(['create', 'edit', 'show']);
        Route::resource('jenis-temuan', JenisTemuanController::class)->except(['create', 'edit', 'show']);
        Route::resource('kategori-temuan', KategoriTemuanController::class)->except(['create', 'edit', 'show']);
        Route::resource('temuan-kolektif', TemuanKolektifController::class)->only(['index', 'store', 'destroy']);
        Route::get('rekap-desk-eval', [RekapDeskEvalController::class, 'index'])->name('rekap-desk-eval.index');
        Route::resource('laporan-ami', LaporanAmiController::class)->except(['create', 'edit', 'show']);
    });

    // ── Pengendalian & Peningkatan ─────────────────────────
    Route::prefix('pengendalian')->name('pengendalian.')->group(function () {
        Route::resource('daftar-temuan', DaftarTemuanController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('kesesuaian', DaftarKesesuaianController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('draft-rtm', DraftRtmController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('upload-rtm', UploadLaporanRtmController::class)->only(['index', 'store', 'destroy']);
    });

    // ── Pengaturan Sistem ──────────────────────────────────
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::resource('pengguna-portal', PenggunaPortalController::class)->except(['create', 'edit', 'show']);
        Route::resource('pengguna-backoffice', PenggunaBackofficeController::class)->except(['create', 'edit', 'show']);
    });
});
