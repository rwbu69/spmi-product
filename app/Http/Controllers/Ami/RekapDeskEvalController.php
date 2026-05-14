<?php

namespace App\Http\Controllers\Ami;

use App\Http\Controllers\Controller;
use App\Models\DeskEvaluation;
use App\Models\EvaluasiDiri;
use App\Models\LembagaAkreditasi;
use App\Models\PengaturanPeriode;
use App\Models\StandarMutu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class RekapDeskEvalController extends Controller
{
    public function index(Request $request): Response
    {
        $isAuditee = auth()->user()?->hasAnyRole(['Auditee', 'Unit Penunjang']);

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

        if ($request->lembaga_id) {
            $query->whereHas('pengaturanPeriode', fn ($q) => $q->where('lembaga_akreditasi_id', $request->lembaga_id));
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        // For Auditee: provide lembaga list with standar count
        $lembagaList = null;
        if ($isAuditee) {
            $lembagaList = LembagaAkreditasi::orderBy('nama_lembaga')->get()->map(fn ($l) => [
                'id'           => $l->id,
                'nama_lembaga' => $l->nama_lembaga,
                'total_standar' => StandarMutu::where('lembaga_akreditasi_id', $l->id)->where('level', 1)->count(),
            ]);
        }

        return Inertia::render('Ami/RekapDeskEval/Index', [
            'data'        => $data,
            'filters'     => $request->only(['search', 'periode_id', 'lembaga_id']),
            'periodeList' => PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(fn ($p) => [
                'id'    => $p->id,
                'label' => "Periode {$p->tahunPeriode->tahun} - {$p->lembagaAkreditasi->nama_lembaga}",
            ]),
            'lembagaList' => $lembagaList,
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

    public function standarMutu(Request $request, $lembagaId): Response
    {
        $lembaga = LembagaAkreditasi::findOrFail($lembagaId);

        // Load top-level standar mutu with recursive children and indikator
        $standarList = StandarMutu::where('lembaga_akreditasi_id', $lembagaId)
            ->whereNull('parent_id')
            ->with(['childrenRecursive.indikator', 'indikator'])
            ->orderBy('kode')
            ->get();

        $buildTree = function ($items) use (&$buildTree) {
            return $items->map(function ($s) use (&$buildTree) {
                $children = $buildTree($s->childrenRecursive ?? collect());
                return [
                    'id'              => $s->id,
                    'kode'            => $s->kode,
                    'nama_standar'    => $s->nama_standar,
                    'level'           => $s->level,
                    'children'        => $children,
                    'total_children'  => $children->count(),
                    'total_indikator' => $s->indikator->count(),
                ];
            })->values();
        };

        return Inertia::render('Ami/RekapDeskEval/StandarMutu', [
            'lembaga'      => ['id' => $lembaga->id, 'nama_lembaga' => $lembaga->nama_lembaga],
            'standarTree'  => $buildTree($standarList),
            'totalStandar' => $standarList->count(),
        ]);
    }

    public function standarDetail(Request $request, $lembagaId, $standarId): Response
    {
        $lembaga = LembagaAkreditasi::findOrFail($lembagaId);
        $standar = StandarMutu::with(['childrenRecursive.indikator', 'indikator'])
            ->findOrFail($standarId);

        $periode = PengaturanPeriode::with('tahunPeriode')
            ->where('lembaga_akreditasi_id', $lembagaId)
            ->orderByDesc('created_at')
            ->first();

        $periodeLabel = $periode?->tahunPeriode?->tahun ?? now()->year;

        $buildNode = function ($s) use (&$buildNode) {
            $children = $s->childrenRecursive ?? collect();
            return [
                'id'              => $s->id,
                'kode'            => $s->kode,
                'nama_standar'    => $s->nama_standar,
                'level'           => $s->level,
                'total_children'  => $children->count(),
                'total_indikator' => $s->indikator->count(),
                'children'        => $children->map($buildNode)->values(),
            ];
        };

        $childrenMapped = ($standar->childrenRecursive ?? collect())->map($buildNode)->values();

        return Inertia::render('Ami/RekapDeskEval/StandarDetail', [
            'lembaga'  => ['id' => $lembaga->id, 'nama_lembaga' => $lembaga->nama_lembaga],
            'standar'  => [
                'id'              => $standar->id,
                'kode'            => $standar->kode,
                'nama_standar'    => $standar->nama_standar,
                'level'           => $standar->level,
                'total_children'  => $childrenMapped->count(),
                'total_indikator' => $standar->indikator->count(),
            ],
            'children' => $childrenMapped,
            'periode'  => (string) $periodeLabel,
        ]);
    }
}
