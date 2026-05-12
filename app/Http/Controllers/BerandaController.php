<?php

namespace App\Http\Controllers;

use App\Models\Auditee;
use App\Models\LembagaAkreditasi;
use App\Models\NilaiMutu;
use App\Models\PengaturanPeriode;
use App\Models\StandarMutu;
use App\Models\TahunPeriode;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BerandaController extends Controller
{
    public function index(Request $request): Response
    {
        $tahunList    = TahunPeriode::orderByDesc('tahun')->get(['id', 'tahun', 'status']);
        $lembagaList  = LembagaAkreditasi::orderBy('nama_lembaga')->get(['id', 'nama_lembaga']);
        $auditeeList  = Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']);

        // Filters
        $filterTahun   = $request->integer('tahun_id');
        $filterLembaga = $request->integer('lembaga_id');
        $filterAuditee = $request->integer('auditee_id');
        $filterJenis   = $request->string('jenis');

        // Summary counts
        $totalAuditee = Auditee::count();
        $totalLembaga = LembagaAkreditasi::count();
        $totalStandar = StandarMutu::where('level', 1)
            ->when($filterLembaga, fn ($q) => $q->where('lembaga_akreditasi_id', $filterLembaga))
            ->count();

        // Chart data: rata-rata nilai mutu per auditee
        $chartQuery = NilaiMutu::with(['auditee:id,nama_auditee', 'pengaturanPeriode.tahunPeriode'])
            ->when($filterTahun, function ($q) use ($filterTahun) {
                $q->whereHas('pengaturanPeriode', fn ($q2) => $q2->where('tahun_periode_id', $filterTahun));
            })
            ->when($filterLembaga, fn ($q) => $q->where('lembaga_akreditasi_id', $filterLembaga))
            ->when($filterAuditee, fn ($q) => $q->where('auditee_id', $filterAuditee));

        $nilaiData = $chartQuery->get()
            ->groupBy('auditee_id')
            ->map(fn ($group) => [
                'auditee'      => $group->first()->auditee->nama_auditee ?? '-',
                'rata_nilai'   => round($group->avg('nilai'), 2),
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
}
