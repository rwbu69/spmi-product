<?php

namespace App\Http\Controllers\Penetapan;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\LembagaAkreditasi;
use App\Models\NilaiMutu;
use App\Models\PengaturanPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NilaiMutuController extends Controller
{
    public function index(Request $request): Response
    {
        $query = NilaiMutu::with(['auditee', 'pengaturanPeriode.tahunPeriode', 'lembagaAkreditasi']);

        if ($request->search) {
            $query->whereHas('auditee', function($q) use ($request) {
                $q->where('nama_auditee', 'like', "%{$request->search}%");
            });
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Penetapan/NilaiMutu/Index', [
            'data' => $data,
            'filters' => $request->only(['search', 'periode_id']),
            'periodeList' => PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(function($p) {
                return [
                    'id' => $p->id,
                    'label' => "Periode {$p->tahunPeriode->tahun} - {$p->lembagaAkreditasi->nama_lembaga}"
                ];
            }),
            'auditeeList' => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
            'lembagaList' => LembagaAkreditasi::orderBy('nama_lembaga')->get(['id', 'nama_lembaga']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auditee_id' => 'required|exists:auditee,id',
            'pengaturan_periode_id' => 'required|exists:pengaturan_periode,id',
            'lembaga_akreditasi_id' => 'required|exists:lembaga_akreditasi,id',
            'nilai' => 'required|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
        ]);

        NilaiMutu::create($validated);

        return back()->with('success', 'Nilai mutu berhasil disimpan.');
    }

    public function update(Request $request, NilaiMutu $nilaiMutu): RedirectResponse
    {
        $validated = $request->validate([
            'nilai' => 'required|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $nilaiMutu->update($validated);

        return back()->with('success', 'Nilai mutu berhasil diperbarui.');
    }

    public function destroy(NilaiMutu $nilaiMutu): RedirectResponse
    {
        $nilaiMutu->delete();
        return back()->with('success', 'Nilai mutu berhasil dihapus.');
    }
}
