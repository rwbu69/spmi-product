<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\PengaturanPeriode;
use App\Models\Auditee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeskEvaluationAuditorController extends Controller
{
    public function index(Request $request): Response
    {
        // Periode beserta jumlah auditee yang punya evaluasi diri di periode tsb
        $periodeList = PengaturanPeriode::with(['lembagaAkreditasi', 'tahunPeriode'])
            ->withCount('evaluasiDiri as auditee_count')
            ->get();

        // Auditee yang sudah mengisi evaluasi diri, dikelompokkan per periode
        // Gunakan evaluasi_diri sebagai jembatan antara Auditee dan PengaturanPeriode
        $auditeeByPeriode = \App\Models\EvaluasiDiri::with('auditee')
            ->select('pengaturan_periode_id', 'auditee_id')
            ->distinct()
            ->get()
            ->groupBy('pengaturan_periode_id')
            ->map(fn ($group) => $group->map(fn ($e) => [
                'id'          => $e->auditee->id,
                'nama_auditee' => $e->auditee->nama_auditee,
            ])->values());

        return Inertia::render('Auditor/DeskEvaluation/Index', [
            'periodeList'     => $periodeList,
            'auditeeByPeriode' => $auditeeByPeriode,
            'filters'         => $request->only(['periode_id']),
        ]);
    }
}
