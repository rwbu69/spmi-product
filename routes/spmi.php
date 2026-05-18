<?php

use App\Http\Controllers\Ami\AuditorController;
use App\Http\Controllers\Ami\JenisTemuanController;
use App\Http\Controllers\Ami\KategoriTemuanController;
use App\Http\Controllers\Ami\LaporanAmiController;
use App\Http\Controllers\Ami\RekapDeskEvalController;
use App\Http\Controllers\Ami\TemuanKolektifController;
use App\Http\Controllers\Auditor\DeskEvaluationAuditorController;
use App\Http\Controllers\Auditor\VisitasiAuditorController;
use App\Http\Controllers\Auditor\DownloadLaporanAmiController;
use App\Http\Controllers\Auditor\UploadLaporanAmiController;
use App\Http\Controllers\Auditor\RekapTemuanAuditorController;
use App\Http\Controllers\Auditor\RekapKesesuaianAuditorController;
use App\Http\Controllers\Admin\SystemNotificationController;
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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BerandaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    // Beranda / Dashboard
    Route::get('/dashboard', [BerandaController::class, 'index'])->name('dashboard');

    // ── Manajemen Referensi ────────────────────────────────
    Route::prefix('referensi')->name('referensi.')->middleware(['role:Admin'])->group(function () {
        Route::resource('tahun-periode', TahunPeriodeController::class)->except(['create', 'edit', 'show']);
        Route::resource('lembaga-akreditasi', LembagaAkreditasiController::class)->except(['create', 'edit', 'show']);
        Route::resource('auditee-pusat', AuditeePusatController::class)->except(['create', 'edit', 'show']);
        Route::resource('auditee', AuditeeController::class)->except(['create', 'edit', 'show']);
        Route::resource('unit-penunjang', UnitPenunjangController::class)->except(['create', 'edit', 'show']);
    });

    // ── Manajemen Dokumen ──────────────────────────────────
    Route::prefix('dokumen')->name('dokumen.')->group(function () {
        Route::middleware(['role:Admin'])->group(function () {
            Route::resource('kategori', KategoriDokumenController::class)->except(['create', 'edit', 'show']);
            Route::resource('jenis', JenisDokumenController::class)->except(['create', 'edit', 'show']);
        });

        Route::middleware(['role:Admin|Auditee|Fakultas|Unit Penunjang'])->group(function () {
            Route::resource('manajemen', ManajemenDokumenController::class)->except(['create', 'edit', 'show']);
            Route::get('manajemen/{manajemen}/download', [ManajemenDokumenController::class, 'download'])->name('manajemen.download');
        });
    });

    // ── Penetapan ──────────────────────────────────────────
    Route::prefix('penetapan')->name('penetapan.')->middleware(['role:Admin'])->group(function () {
        Route::resource('nilai-mutu', NilaiMutuController::class)->except(['create', 'edit', 'show']);
        Route::resource('standar-mutu', StandarMutuController::class)->except(['create', 'edit']);
        Route::resource('indikator', IndikatorController::class)->only(['store', 'update', 'destroy']);
    });

    // ── Pelaksanaan ────────────────────────────────────────
    Route::prefix('pelaksanaan')->name('pelaksanaan.')->group(function () {
        Route::middleware(['role:Admin|Auditee|Unit Penunjang'])->group(function () {
            Route::resource('pengaturan-periode', PengaturanPeriodeController::class)->except(['create', 'edit', 'show']);
            Route::resource('target-nilai', TargetNilaiMutuController::class)->except(['create', 'edit', 'show']);
        });

        Route::middleware(['role:Admin|Auditee|Unit Penunjang|Fakultas'])->group(function () {
            Route::resource('evaluasi-diri', EvaluasiDiriController::class)->only(['index', 'store', 'destroy']);
        });
    });

    // ── Evaluasi AMI (Admin only) ──────────────────────────
    Route::prefix('ami')->name('ami.')->middleware(['role:Admin'])->group(function () {
        Route::resource('auditor', AuditorController::class)->except(['create', 'edit', 'show']);
        Route::resource('jenis-temuan', JenisTemuanController::class)->except(['create', 'edit', 'show']);
        Route::resource('kategori-temuan', KategoriTemuanController::class)->except(['create', 'edit', 'show']);
        Route::resource('temuan-kolektif', TemuanKolektifController::class)->only(['index', 'store', 'destroy']);
        Route::resource('laporan-ami', LaporanAmiController::class)->except(['create', 'edit', 'show']);
    });

    // ── Evaluasi AMI (Admin + Fakultas + Auditee + Unit Penunjang) ───────────────────────
    Route::prefix('ami')->name('ami.view.')->group(function () {
        Route::middleware(['role:Admin|Auditor|Fakultas|Auditee|Unit Penunjang'])->group(function () {
            Route::get('rekap-desk-eval', [RekapDeskEvalController::class, 'index'])->name('rekap-desk-eval.index');
            Route::get('rekap-desk-eval/standar/{lembagaId}', [RekapDeskEvalController::class, 'standarMutu'])->name('rekap-desk-eval.standar');
            Route::get('rekap-desk-eval/standar/{lembagaId}/detail/{standarId}', [RekapDeskEvalController::class, 'standarDetail'])->name('rekap-desk-eval.standar-detail');
            Route::get('rekap-desk-eval/{id}', [RekapDeskEvalController::class, 'show'])->name('rekap-desk-eval.show');
        });

        Route::middleware(['role:Admin|Auditor|Auditee|Unit Penunjang'])->group(function () {
            Route::get('laporan-ami', [LaporanAmiController::class, 'index'])->name('laporan-ami.index');
            Route::get('laporan-ami/{laporanAmi}/download', [LaporanAmiController::class, 'download'])->name('laporan-ami.download');
        });
    });

    // ── Auditor Menu ───────────────────────────────────────
    Route::prefix('auditor')->name('auditor.')->middleware(['role:Auditor'])->group(function () {
        Route::get('desk-evaluation', [DeskEvaluationAuditorController::class, 'index'])->name('desk-evaluation.index');
        Route::get('visitasi', [VisitasiAuditorController::class, 'index'])->name('visitasi.index');
        Route::get('download-laporan-ami', [DownloadLaporanAmiController::class, 'index'])->name('download-laporan-ami.index');
        Route::get('upload-laporan-ami', [UploadLaporanAmiController::class, 'index'])->name('upload-laporan-ami.index');
        Route::post('upload-laporan-ami', [UploadLaporanAmiController::class, 'store'])->name('upload-laporan-ami.store');
        Route::delete('upload-laporan-ami/{laporanAmi}', [UploadLaporanAmiController::class, 'destroy'])->name('upload-laporan-ami.destroy');
        Route::get('rekap-temuan', [RekapTemuanAuditorController::class, 'index'])->name('rekap-temuan.index');
        Route::get('rekap-kesesuaian', [RekapKesesuaianAuditorController::class, 'index'])->name('rekap-kesesuaian.index');
    });

    // ── Pengendalian & Peningkatan ─────────────────────────
    Route::prefix('pengendalian')->name('pengendalian.')->group(function () {
        Route::middleware(['role:Admin|Auditor|Auditee|Fakultas|Unit Penunjang'])->group(function () {
            Route::resource('daftar-temuan', DaftarTemuanController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::get('daftar-temuan/export', [DaftarTemuanController::class, 'export'])->name('daftar-temuan.export');
        });

        Route::middleware(['role:Admin|Auditor|Auditee|Unit Penunjang'])->group(function () {
            Route::get('draft-rtm/{draft_rtm}/download', [DraftRtmController::class, 'download'])->name('draft-rtm.download');
            Route::resource('draft-rtm', DraftRtmController::class)->only(['index', 'store', 'update', 'destroy']);
            
            Route::get('upload-rtm/{upload_rtm}/download', [UploadLaporanRtmController::class, 'download'])->name('upload-rtm.download');
            Route::resource('upload-rtm', UploadLaporanRtmController::class)->only(['index', 'store', 'destroy']);
        });
    });

    // Kesesuaian — accessible by Admin, Auditor, Auditee, Fakultas
    Route::prefix('pengendalian')->name('pengendalian.')->middleware(['role:Admin|Auditor|Auditee|Fakultas'])->group(function () {
        Route::resource('kesesuaian', DaftarKesesuaianController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('kesesuaian/export', [DaftarKesesuaianController::class, 'export'])->name('kesesuaian.export');
    });

    // ── Pengaturan Sistem ──────────────────────────────────
    Route::prefix('pengaturan')->name('pengaturan.')->middleware(['role:Admin'])->group(function () {
        Route::resource('pengguna-portal', PenggunaPortalController::class)
            ->parameters(['pengguna-portal' => 'user'])
            ->except(['create', 'edit', 'show']);
        Route::resource('pengguna-backoffice', PenggunaBackofficeController::class)
            ->parameters(['pengguna-backoffice' => 'user'])
            ->except(['create', 'edit', 'show']);
    });

    // ── Admin System Notifications ─────────────────────────
    Route::prefix('admin')->name('admin.')->middleware(['role:Admin'])->group(function () {
        Route::get('log-sistem', [SystemNotificationController::class, 'index'])->name('log-sistem.index');
    });

    // ── Notifications Read State ───────────────────────────
    Route::post('notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});
