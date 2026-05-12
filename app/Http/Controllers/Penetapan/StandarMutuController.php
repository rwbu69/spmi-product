<?php

namespace App\Http\Controllers\Penetapan;

use App\Http\Controllers\Controller;
use App\Models\LembagaAkreditasi;
use App\Models\StandarMutu;
use App\Models\TahunPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StandarMutuController extends Controller
{
    public function index(Request $request): Response
    {
        $query = StandarMutu::with(['lembagaAkreditasi', 'tahunPeriode'])
            ->whereNull('parent_id'); // Get top-level standards

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('kode', 'like', "%{$request->search}%")
                  ->orWhere('nama_standar', 'like', "%{$request->search}%");
            });
        }

        if ($request->lembaga_id) {
            $query->where('lembaga_akreditasi_id', $request->lembaga_id);
        }

        if ($request->tahun_id) {
            $query->where('tahun_periode_id', $request->tahun_id);
        }

        $data = $query->orderBy('kode')->paginate(10)->withQueryString();

        return Inertia::render('Penetapan/StandarMutu/Index', [
            'data' => $data,
            'filters' => $request->only(['search', 'lembaga_id', 'tahun_id']),
            'lembagaList' => LembagaAkreditasi::orderBy('nama_lembaga')->get(['id', 'nama_lembaga']),
            'tahunList' => TahunPeriode::orderByDesc('tahun')->get(['id', 'tahun', 'status']),
        ]);
    }

    public function show(StandarMutu $standarMutu): Response
    {
        $standarMutu->load(['childrenRecursive.indikator', 'indikator', 'lembagaAkreditasi', 'tahunPeriode']);
        
        return Inertia::render('Penetapan/StandarMutu/Show', [
            'standar' => $standarMutu
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50',
            'nama_standar' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:standar_mutu,id',
            'lembaga_akreditasi_id' => 'required|exists:lembaga_akreditasi,id',
            'tahun_periode_id' => 'required|exists:tahun_periode,id',
            'level' => 'required|integer',
            'data_dukung' => 'nullable|string',
            'deskripsi' => 'nullable|string',
        ]);

        StandarMutu::create($validated);

        return back()->with('success', 'Standar mutu berhasil ditambahkan.');
    }

    public function update(Request $request, StandarMutu $standarMutu): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50',
            'nama_standar' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:standar_mutu,id',
            'lembaga_akreditasi_id' => 'required|exists:lembaga_akreditasi,id',
            'tahun_periode_id' => 'required|exists:tahun_periode,id',
            'level' => 'required|integer',
            'data_dukung' => 'nullable|string',
            'deskripsi' => 'nullable|string',
        ]);

        $standarMutu->update($validated);

        return back()->with('success', 'Standar mutu berhasil diperbarui.');
    }

    public function destroy(StandarMutu $standarMutu): RedirectResponse
    {
        if ($standarMutu->children()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus standar yang memiliki sub-standar.');
        }

        $standarMutu->delete();

        return back()->with('success', 'Standar mutu berhasil dihapus.');
    }
}
