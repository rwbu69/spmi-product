<?php

namespace App\Http\Controllers\Ami;

use App\Http\Controllers\Controller;
use App\Models\DeskEvaluation;
use App\Models\EvaluasiDiri;
use App\Models\PengaturanPeriode;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class RekapDeskEvalController extends Controller
{
    public function index(Request $request): Response
    {
        $query = EvaluasiDiri::with(['auditee', 'pengaturanPeriode.tahunPeriode', 'pengaturanPeriode.lembagaAkreditasi'])
            ->select('evaluasi_diri.*')
            ->addSelect([
                'avg_desk_eval' => DeskEvaluation::whereColumn('evaluasi_diri_id', 'evaluasi_diri.id')
                    ->selectRaw('avg(nilai)')
            ]);

        if ($request->search) {
            $query->whereHas('auditee', function($q) use ($request) {
                $q->where('nama_auditee', 'like', "%{$request->search}%");
            });
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Ami/RekapDeskEval/Index', [
            'data' => $data,
            'filters' => $request->only(['search', 'periode_id']),
            'periodeList' => PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(function($p) {
                return [
                    'id' => $p->id,
                    'label' => "Periode {$p->tahunPeriode->tahun} - {$p->lembagaAkreditasi->nama_lembaga}"
                ];
            }),
        ]);
    }

    public function show(Request $request, $id): Response
    {
        $evaluasiDiri = EvaluasiDiri::with(['auditee', 'pengaturanPeriode.tahunPeriode', 'pengaturanPeriode.lembagaAkreditasi'])
            ->findOrFail($id);

        $deskEvaluations = DeskEvaluation::with(['auditor', 'indikator.standarMutu'])
            ->where('evaluasi_diri_id', $id)
            ->get();

        return Inertia::render('Ami/RekapDeskEval/Show', [
            'evaluasiDiri' => $evaluasiDiri,
            'deskEvaluations' => $deskEvaluations,
        ]);
    }
}
