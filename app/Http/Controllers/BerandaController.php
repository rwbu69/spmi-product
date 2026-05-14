<?php

namespace App\Http\Controllers;

use App\Models\Auditee;
use App\Models\EvaluasiDiri;
use App\Models\LaporanAmi;
use App\Models\LembagaAkreditasi;
use App\Models\NilaiMutu;
use App\Models\PengaturanPeriode;
use App\Models\StandarMutu;
use App\Models\TahunPeriode;
use App\Models\TargetNilaiMutu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BerandaController extends Controller
{
    public function index(Request $request): Response
    {
        // ── Beranda khusus Auditor ────────────────────────────────
        if (auth()->user()?->hasRole('Auditor')) {
            return $this->indexAuditor();
        }

        // ── Beranda khusus Auditee / Unit Penunjang ────────────────
        if (auth()->user()?->hasAnyRole(['Auditee', 'Unit Penunjang'])) {
            return $this->indexAuditee($request);
        }

        // ── Beranda default (Admin, Fakultas) ──────────────────────
        $tahunList    = TahunPeriode::orderByDesc('tahun')->get(['id', 'tahun', 'status']);
        $lembagaList  = LembagaAkreditasi::orderBy('nama_lembaga')->get(['id', 'nama_lembaga']);
        $auditeeList  = Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']);

        $filterTahun   = $request->integer('tahun_id');
        $filterLembaga = $request->integer('lembaga_id');
        $filterAuditee = $request->integer('auditee_id');
        $filterJenis   = $request->string('jenis');

        $totalAuditee = Auditee::count();
        $totalLembaga = LembagaAkreditasi::count();
        $totalStandar = StandarMutu::where('level', 1)
            ->when($filterLembaga, fn ($q) => $q->where('lembaga_akreditasi_id', $filterLembaga))
            ->count();

        $chartQuery = NilaiMutu::with(['auditee:id,nama_auditee', 'pengaturanPeriode.tahunPeriode'])
            ->when($filterTahun, function ($q) use ($filterTahun) {
                $q->whereHas('pengaturanPeriode', fn ($q2) => $q2->where('tahun_periode_id', $filterTahun));
            })
            ->when($filterLembaga, fn ($q) => $q->where('lembaga_akreditasi_id', $filterLembaga))
            ->when($filterAuditee, fn ($q) => $q->where('auditee_id', $filterAuditee));

        $nilaiData = $chartQuery->get()
            ->groupBy('auditee_id')
            ->map(fn ($group) => [
                'auditee'    => $group->first()->auditee->nama_auditee ?? '-',
                'rata_nilai' => round($group->avg('nilai'), 2),
            ])
            ->values();

        return Inertia::render('Beranda/Index', [
            'summary' => [
                'total_auditee' => $totalAuditee,
                'total_lembaga' => $totalLembaga,
                'total_standar' => $totalStandar,
            ],
            'chartData'   => $nilaiData,
            'tahunList'   => $tahunList,
            'lembagaList' => $lembagaList,
            'auditeeList' => $auditeeList,
            'filters'     => [
                'tahun_id'   => $filterTahun ?: null,
                'lembaga_id' => $filterLembaga ?: null,
                'auditee_id' => $filterAuditee ?: null,
                'jenis'      => $filterJenis ?: null,
            ],
        ]);
    }

    private function indexAuditee(Request $request): Response
    {
        $lembagaList  = LembagaAkreditasi::orderBy('nama_lembaga')->get(['id', 'nama_lembaga']);
        $tahunList    = TahunPeriode::orderByDesc('tahun')->get(['id', 'tahun', 'status']);
        $standarList  = StandarMutu::where('level', 1)->orderBy('kode')->get(['id', 'kode', 'nama_standar']);

        $filterLembaga = $request->integer('lembaga_id');
        $filterTahun   = $request->integer('tahun_id');

        // Filter periode based on selections
        $periodeQuery = PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])
            ->when($filterLembaga, fn ($q) => $q->where('lembaga_akreditasi_id', $filterLembaga))
            ->when($filterTahun, fn ($q) => $q->where('tahun_periode_id', $filterTahun));

        $periodeIds = $periodeQuery->pluck('id');

        // Stats
        $targetNilai = TargetNilaiMutu::when($periodeIds->isNotEmpty(), fn ($q) => $q->whereIn('pengaturan_periode_id', $periodeIds))
            ->avg('target_nilai');

        $nilaiEvaluasiDiri = EvaluasiDiri::when($periodeIds->isNotEmpty(), fn ($q) => $q->whereIn('pengaturan_periode_id', $periodeIds))
            ->avg('nilai_evaluasi');

        $nilaiHasilAudit = NilaiMutu::when($periodeIds->isNotEmpty(), fn ($q) => $q->whereHas('pengaturanPeriode', fn ($q2) => $q2->whereIn('id', $periodeIds)))
            ->when($filterLembaga, fn ($q) => $q->where('lembaga_akreditasi_id', $filterLembaga))
            ->avg('nilai');

        // Chart: Nilai per lembaga akreditasi (NilaiMutu has no standar_mutu_id column)
        $chartData = NilaiMutu::with('lembagaAkreditasi')
            ->when($periodeIds->isNotEmpty(), fn ($q) => $q->whereHas('pengaturanPeriode', fn ($q2) => $q2->whereIn('id', $periodeIds)))
            ->when($filterLembaga, fn ($q) => $q->where('lembaga_akreditasi_id', $filterLembaga))
            ->get()
            ->groupBy('lembaga_akreditasi_id')
            ->map(fn ($group) => [
                'standar' => $group->first()->lembagaAkreditasi->nama_lembaga ?? '-',
                'nilai'   => round($group->avg('nilai'), 2),
            ])
            ->values();

        return Inertia::render('Beranda/Auditee', [
            'stats' => [
                'target_nilai'       => round($targetNilai ?? 0, 2),
                'nilai_evaluasi_diri' => round($nilaiEvaluasiDiri ?? 0, 2),
                'nilai_hasil_audit'  => round($nilaiHasilAudit ?? 0, 2),
            ],
            'chartData'   => $chartData,
            'lembagaList' => $lembagaList,
            'tahunList'   => $tahunList,
            'standarList' => $standarList,
            'filters'     => [
                'lembaga_id' => $filterLembaga ?: null,
                'tahun_id'   => $filterTahun ?: null,
            ],
        ]);
    }

    private function indexAuditor(): Response
    {
        // Total evaluasi_diri yang terdaftar
        $totalEvaluasiDiri = EvaluasiDiri::count();

        // Sudah Desk Evaluation = evaluasi_diri yang punya minimal 1 record desk_evaluation
        $sudahDeskEval = EvaluasiDiri::whereHas('deskEvaluasi')->count();

        // Belum Desk Evaluation = sisanya
        $belumDeskEval = $totalEvaluasiDiri - $sudahDeskEval;

        // Total auditee yang punya evaluasi_diri aktif (via periode aktif)
        $totalAuditeeAktif = EvaluasiDiri::whereHas('pengaturanPeriode', fn ($q) =>
            $q->where('status', 'Aktif')
        )->count();

        // Sudah Visitasi = auditee yang sudah punya laporan_ami
        $sudahVisitasi = LaporanAmi::distinct('auditee_id')->count('auditee_id');

        // Belum Visitasi = evaluasi_diri aktif yang auditee-nya belum punya laporan_ami
        $auditeeIdsWithLaporan = LaporanAmi::distinct()->pluck('auditee_id');
        $belumVisitasi = EvaluasiDiri::whereHas('pengaturanPeriode', fn ($q) =>
            $q->where('status', 'Aktif')
        )->whereNotIn('auditee_id', $auditeeIdsWithLaporan)->count();

        return Inertia::render('Beranda/Auditor', [
            'stats' => [
                'belum_desk_eval' => $belumDeskEval,
                'sudah_desk_eval' => $sudahDeskEval,
                'belum_visitasi'  => $belumVisitasi,
                'sudah_visitasi'  => $sudahVisitasi,
            ],
        ]);
    }
}
