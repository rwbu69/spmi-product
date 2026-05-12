<?php

namespace App\Http\Controllers\Pelaksanaan;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\PengaturanPeriode;
use App\Models\TargetNilaiMutu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TargetNilaiMutuController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TargetNilaiMutu::with(['auditee', 'pengaturanPeriode.tahunPeriode', 'pengaturanPeriode.lembagaAkreditasi']);

        if ($request->search) {
            $query->whereHas('auditee', function($q) use ($request) {
                $q->where('nama_auditee', 'like', "%{$request->search}%");
            });
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pelaksanaan/TargetNilai/Index', [
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
            'target_nilai' => 'required|numeric|min:0|max:100',
        ]);

        // Unique check handled by database unique constraint, but let's be nice
        $exists = TargetNilaiMutu::where('pengaturan_periode_id', $validated['pengaturan_periode_id'])
            ->where('auditee_id', $validated['auditee_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Target nilai untuk auditee pada periode ini sudah ada.');
        }

        TargetNilaiMutu::create($validated);

        return back()->with('success', 'Target nilai berhasil ditambahkan.');
    }

    public function update(Request $request, TargetNilaiMutu $targetNilaiMutu): RedirectResponse
    {
        $validated = $request->validate([
            'target_nilai' => 'required|numeric|min:0|max:100',
        ]);

        $targetNilaiMutu->update($validated);

        return back()->with('success', 'Target nilai berhasil diperbarui.');
    }

    public function destroy(TargetNilaiMutu $targetNilaiMutu): RedirectResponse
    {
        $targetNilaiMutu->delete();

        return back()->with('success', 'Target nilai berhasil dihapus.');
    }
}
