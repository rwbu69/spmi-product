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
        $periodeList = PengaturanPeriode::with(['lembagaAkreditasi', 'tahunPeriode'])
            ->get()
            ->map(function ($periode) {
                $periode->auditee_count = Auditee::where('pengaturan_periode_id', $periode->id)->count();
                return $periode;
            });

        return Inertia::render('Auditor/DeskEvaluation/Index', [
            'periodeList' => $periodeList,
            'filters'     => $request->only(['periode_id']),
            'auditeeList' => Auditee::orderBy('nama_auditee')
                ->get(['id', 'nama_auditee', 'pengaturan_periode_id']),
        ]);
    }
}
