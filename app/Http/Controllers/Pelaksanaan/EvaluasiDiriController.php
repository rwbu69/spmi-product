<?php

namespace App\Http\Controllers\Pelaksanaan;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\EvaluasiDiri;
use App\Models\PengaturanPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EvaluasiDiriController extends Controller
{
    public function index(Request $request): Response
    {
        $query = EvaluasiDiri::with(['auditee', 'pengaturanPeriode.tahunPeriode', 'pengaturanPeriode.lembagaAkreditasi']);

        if ($request->search) {
            $query->whereHas('auditee', function($q) use ($request) {
                $q->where('nama_auditee', 'like', "%{$request->search}%");
            });
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pelaksanaan/EvaluasiDiri/Index', [
            'data' => $data,
            'filters' => $request->only(['search', 'periode_id']),
            'periodeList' => PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(function($p) {
                return [
                    'id' => $p->id,
                    'label' => "Periode {$p->tahunPeriode->tahun} - {$p->lembagaAkreditasi->nama_lembaga}"
                ];
            }),
            'auditeeList' => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pengaturan_periode_id' => 'required|exists:pengaturan_periode,id',
            'auditee_id' => 'required|exists:auditee,id',
            'status' => 'required|in:Draft,Submitted,Approved',
        ]);

        EvaluasiDiri::updateOrCreate(
            ['pengaturan_periode_id' => $validated['pengaturan_periode_id'], 'auditee_id' => $validated['auditee_id']],
            ['status' => $validated['status']]
        );

        return back()->with('success', 'Evaluasi diri berhasil diinisialisasi.');
    }

    public function destroy(EvaluasiDiri $evaluasiDiri): RedirectResponse
    {
        $evaluasiDiri->delete();
        return back()->with('success', 'Evaluasi diri berhasil dihapus.');
    }
}
