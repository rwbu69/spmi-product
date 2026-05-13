<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\PengaturanPeriode;
use App\Models\Auditee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VisitasiAuditorController extends Controller
{
    public function index(Request $request): Response
    {
        $periodeList = PengaturanPeriode::with(['lembagaAkreditasi', 'tahunPeriode'])
            ->withCount('evaluasiDiri as auditee_count')
            ->get();

        // Sama seperti DeskEvaluation: gunakan evaluasi_diri sebagai jembatan
        $auditeeByPeriode = \App\Models\EvaluasiDiri::with('auditee')
            ->select('pengaturan_periode_id', 'auditee_id')
            ->distinct()
            ->get()
            ->groupBy('pengaturan_periode_id')
            ->map(fn ($group) => $group->map(fn ($e) => [
                'id'           => $e->auditee->id,
                'nama_auditee' => $e->auditee->nama_auditee,
            ])->values());

        return Inertia::render('Auditor/Visitasi/Index', [
            'periodeList'     => $periodeList,
            'auditeeByPeriode' => $auditeeByPeriode,
            'filters'         => $request->only(['periode_id']),
        ]);
    }
}
