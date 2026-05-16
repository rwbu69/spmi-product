<?php

use App\Models\Auditee;
use App\Models\AuditeePusat;
use App\Models\Auditor;
use App\Models\DaftarKesesuaian;
use App\Models\DaftarTemuanPp;
use App\Models\DeskEvaluation;
use App\Models\DraftLaporanRtm;
use App\Models\EvaluasiDiri;
use App\Models\Indikator;
use App\Models\JenisDokumen;
use App\Models\JenisTemuan;
use App\Models\KategoriDokumen;
use App\Models\KategoriTemuan;
use App\Models\LaporanAmi;
use App\Models\LembagaAkreditasi;
use App\Models\ManajemenDokumen;
use App\Models\NilaiMutu;
use App\Models\PengaturanPeriode;
use App\Models\RekapTemuanApproval;
use App\Models\RencanaTindakLanjut;
use App\Models\StandarMutu;
use App\Models\TahunPeriode;
use App\Models\TargetNilaiMutu;
use App\Models\Temuan;
use App\Models\UnitPenunjang;
use App\Models\UploadLaporanRtm;
use App\Models\User;

return [
    'modules' => [
        // Referensi
        'referensi_tahun_periode' => [
            'label' => 'Tahun Periode',
            'roles' => ['Admin'],
            'route' => 'referensi.tahun-periode.index',
            'color' => 'blue',
        ],
        'referensi_lembaga_akreditasi' => [
            'label' => 'Lembaga Akreditasi',
            'roles' => ['Admin'],
            'route' => 'referensi.lembaga-akreditasi.index',
            'color' => 'blue',
        ],
        'referensi_auditee_pusat' => [
            'label' => 'Auditee Pusat',
            'roles' => ['Admin'],
            'route' => 'referensi.auditee-pusat.index',
            'color' => 'blue',
        ],
        'referensi_auditee' => [
            'label' => 'Auditee',
            'roles' => ['Admin'],
            'route' => 'referensi.auditee.index',
            'color' => 'blue',
        ],
        'referensi_unit_penunjang' => [
            'label' => 'Unit Penunjang',
            'roles' => ['Admin'],
            'route' => 'referensi.unit-penunjang.index',
            'color' => 'blue',
        ],

        // Dokumen
        'dokumen_kategori' => [
            'label' => 'Kategori Dokumen',
            'roles' => ['Admin', 'Auditee', 'Fakultas', 'Unit Penunjang'],
            'route' => 'dokumen.kategori.index',
            'color' => 'amber',
        ],
        'dokumen_jenis' => [
            'label' => 'Jenis Dokumen',
            'roles' => ['Admin', 'Auditee', 'Fakultas', 'Unit Penunjang'],
            'route' => 'dokumen.jenis.index',
            'color' => 'amber',
        ],
        'dokumen_manajemen' => [
            'label' => 'Manajemen Dokumen',
            'roles' => ['Admin', 'Auditee', 'Fakultas', 'Unit Penunjang'],
            'route' => 'dokumen.manajemen.index',
            'color' => 'amber',
        ],

        // Penetapan
        'penetapan_nilai_mutu' => [
            'label' => 'Nilai Mutu',
            'roles' => ['Admin'],
            'route' => 'penetapan.nilai-mutu.index',
            'color' => 'green',
        ],
        'penetapan_standar_mutu' => [
            'label' => 'Standar Mutu',
            'roles' => ['Admin'],
            'route' => 'penetapan.standar-mutu.index',
            'color' => 'green',
        ],

        // Pelaksanaan
        'pelaksanaan_pengaturan_periode' => [
            'label' => 'Pengaturan Periode',
            'roles' => ['Admin', 'Auditee', 'Unit Penunjang'],
            'route' => 'pelaksanaan.pengaturan-periode.index',
            'color' => 'green',
        ],
        'pelaksanaan_target_nilai' => [
            'label' => 'Target Nilai Mutu',
            'roles' => ['Admin', 'Auditee', 'Unit Penunjang'],
            'route' => 'pelaksanaan.target-nilai.index',
            'color' => 'green',
        ],
        'pelaksanaan_evaluasi_diri' => [
            'label' => 'Evaluasi Diri',
            'roles' => ['Admin', 'Auditee', 'Unit Penunjang'],
            'route' => 'pelaksanaan.evaluasi-diri.index',
            'color' => 'green',
        ],

        // Evaluasi AMI (Admin)
        'ami_auditor' => [
            'label' => 'Manajemen Auditor',
            'roles' => ['Admin'],
            'route' => 'ami.auditor.index',
            'color' => 'blue',
        ],
        'ami_jenis_temuan' => [
            'label' => 'Jenis Temuan',
            'roles' => ['Admin'],
            'route' => 'ami.jenis-temuan.index',
            'color' => 'blue',
        ],
        'ami_kategori_temuan' => [
            'label' => 'Kategori Temuan',
            'roles' => ['Admin'],
            'route' => 'ami.kategori-temuan.index',
            'color' => 'blue',
        ],
        'ami_temuan_kolektif' => [
            'label' => 'Temuan Kolektif',
            'roles' => ['Admin'],
            'route' => 'ami.temuan-kolektif.index',
            'color' => 'blue',
        ],
        'ami_rekap_desk_eval' => [
            'label' => 'Rekap Desk Evaluation',
            'roles' => ['Admin', 'Fakultas', 'Auditee', 'Unit Penunjang'],
            'route' => 'ami.view.rekap-desk-eval.index',
            'color' => 'blue',
        ],
        'ami_laporan_ami' => [
            'label' => 'Laporan AMI',
            'roles' => ['Admin', 'Fakultas', 'Auditee', 'Unit Penunjang', 'Auditor'],
            'route_by_role' => [
                'Admin' => 'ami.laporan-ami.index',
                'Fakultas' => 'ami.view.laporan-ami.index',
                'Auditee' => 'ami.view.laporan-ami.index',
                'Unit Penunjang' => 'ami.view.laporan-ami.index',
                'Auditor' => 'auditor.download-laporan-ami.index',
            ],
            'color' => 'amber',
        ],

        // Auditor menu
        'auditor_desk_evaluation' => [
            'label' => 'Desk Evaluation',
            'roles' => ['Auditor'],
            'route' => 'auditor.desk-evaluation.index',
            'color' => 'blue',
        ],
        'auditor_visitasi' => [
            'label' => 'Visitasi',
            'roles' => ['Auditor'],
            'route' => 'auditor.visitasi.index',
            'color' => 'blue',
        ],
        'auditor_upload_laporan_ami' => [
            'label' => 'Upload Laporan AMI',
            'roles' => ['Auditor'],
            'route' => 'auditor.upload-laporan-ami.index',
            'color' => 'blue',
        ],
        'auditor_download_laporan_ami' => [
            'label' => 'Download Laporan AMI',
            'roles' => ['Auditor'],
            'route' => 'auditor.download-laporan-ami.index',
            'color' => 'blue',
        ],
        'auditor_rekap_temuan' => [
            'label' => 'Rekap Temuan',
            'roles' => ['Auditor'],
            'route' => 'auditor.rekap-temuan.index',
            'color' => 'blue',
        ],
        'auditor_rekap_kesesuaian' => [
            'label' => 'Rekap Kesesuaian',
            'roles' => ['Auditor'],
            'route' => 'auditor.rekap-kesesuaian.index',
            'color' => 'blue',
        ],

        // Pengendalian
        'pengendalian_daftar_temuan' => [
            'label' => 'Daftar Temuan',
            'roles' => ['Admin', 'Auditor', 'Auditee', 'Fakultas', 'Unit Penunjang'],
            'route' => 'pengendalian.daftar-temuan.index',
            'color' => 'green',
        ],
        'pengendalian_kesesuaian' => [
            'label' => 'Daftar Kesesuaian',
            'roles' => ['Admin', 'Auditor', 'Auditee', 'Fakultas'],
            'route' => 'pengendalian.kesesuaian.index',
            'color' => 'green',
        ],
        'pengendalian_draft_rtm' => [
            'label' => 'Draft Laporan RTM',
            'roles' => ['Admin', 'Auditor', 'Auditee', 'Fakultas', 'Unit Penunjang'],
            'route' => 'pengendalian.draft-rtm.index',
            'color' => 'green',
        ],
        'pengendalian_upload_rtm' => [
            'label' => 'Upload Laporan RTM',
            'roles' => ['Admin', 'Auditor', 'Auditee', 'Fakultas', 'Unit Penunjang'],
            'route' => 'pengendalian.upload-rtm.index',
            'color' => 'green',
        ],

        // Pengaturan
        'pengaturan_pengguna_backoffice' => [
            'label' => 'Pengguna Backoffice',
            'roles' => ['Admin'],
            'route' => 'pengaturan.pengguna-backoffice.index',
            'color' => 'blue',
        ],
        'pengaturan_pengguna_portal' => [
            'label' => 'Pengguna Portal',
            'roles' => ['Admin'],
            'route' => 'pengaturan.pengguna-portal.index',
            'color' => 'blue',
        ],
    ],

    'model_map' => [
        TahunPeriode::class => 'referensi_tahun_periode',
        LembagaAkreditasi::class => 'referensi_lembaga_akreditasi',
        AuditeePusat::class => 'referensi_auditee_pusat',
        Auditee::class => 'referensi_auditee',
        UnitPenunjang::class => 'referensi_unit_penunjang',

        KategoriDokumen::class => 'dokumen_kategori',
        JenisDokumen::class => 'dokumen_jenis',
        ManajemenDokumen::class => 'dokumen_manajemen',

        NilaiMutu::class => 'penetapan_nilai_mutu',
        StandarMutu::class => 'penetapan_standar_mutu',
        Indikator::class => 'penetapan_standar_mutu',

        PengaturanPeriode::class => 'pelaksanaan_pengaturan_periode',
        TargetNilaiMutu::class => 'pelaksanaan_target_nilai',
        EvaluasiDiri::class => 'pelaksanaan_evaluasi_diri',

        Auditor::class => 'ami_auditor',
        JenisTemuan::class => 'ami_jenis_temuan',
        KategoriTemuan::class => 'ami_kategori_temuan',
        Temuan::class => 'ami_temuan_kolektif',

        DeskEvaluation::class => 'auditor_desk_evaluation',

        LaporanAmi::class => 'ami_laporan_ami',

        DaftarTemuanPp::class => 'pengendalian_daftar_temuan',
        RekapTemuanApproval::class => 'pengendalian_daftar_temuan',
        DaftarKesesuaian::class => 'pengendalian_kesesuaian',
        DraftLaporanRtm::class => 'pengendalian_draft_rtm',
        UploadLaporanRtm::class => 'pengendalian_upload_rtm',
        RencanaTindakLanjut::class => 'pengendalian_daftar_temuan',

        User::class => 'pengaturan_pengguna_portal',
    ],
];
