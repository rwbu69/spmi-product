<?php

namespace App\Http\Controllers\Ami;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\PengaturanPeriode;
use App\Models\RekapTemuanApproval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TemuanKolektifController extends Controller
{
    public function index(Request $request): Response
    {
        $query = RekapTemuanApproval::with(['auditee', 'pengaturanPeriode.tahunPeriode', 'pengaturanPeriode.lembagaAkreditasi']);

        if ($request->search) {
            $query->whereHas('auditee', function($q) use ($request) {
                $q->where('nama_auditee', 'like', "%{$request->search}%");
            });
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Ami/TemuanKolektif/Index', [
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
            'auditee_id' => 'required|exists:auditee,id',
            'pengaturan_periode_id' => 'required|exists:pengaturan_periode,id',
            'status_approval' => 'required|in:Pending,Approved',
            'jumlah_temuan' => 'required|integer|min:0',
        ]);

        RekapTemuanApproval::updateOrCreate(
            ['auditee_id' => $validated['auditee_id'], 'pengaturan_periode_id' => $validated['pengaturan_periode_id']],
            [
                'status_approval' => $validated['status_approval'],
                'jumlah_temuan' => $validated['jumlah_temuan'],
                'tanggal_approval' => $validated['status_approval'] === 'Approved' ? now() : null,
                'approved_by' => $validated['status_approval'] === 'Approved' ? auth()->user()->name : null,
            ]
        );

        return back()->with('success', 'Rekap temuan berhasil diperbarui.');
    }

    public function destroy(RekapTemuanApproval $temuanKolektif): RedirectResponse
    {
        $temuanKolektif->delete();
        return back()->with('success', 'Rekap temuan berhasil dihapus.');
    }
}
