<?php

namespace App\Http\Controllers\Pelaksanaan;

use App\Http\Controllers\Controller;
use App\Models\LembagaAkreditasi;
use App\Models\PengaturanPeriode;
use App\Models\TahunPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PengaturanPeriodeController extends Controller
{
    public function index(Request $request): Response
    {
        $data = PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Pelaksanaan/PengaturanPeriode/Index', [
            'data' => $data,
            'tahunList' => TahunPeriode::orderByDesc('tahun')->get(['id', 'tahun']),
            'lembagaList' => LembagaAkreditasi::orderBy('nama_lembaga')->get(['id', 'nama_lembaga']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_periode_id' => 'required|exists:tahun_periode,id',
            'lembaga_akreditasi_id' => 'required|exists:lembaga_akreditasi,id',
            'mulai_evaluasi_diri' => 'nullable|date',
            'akhir_evaluasi_diri' => 'nullable|date',
            'mulai_desk_eval' => 'nullable|date',
            'akhir_desk_eval' => 'nullable|date',
            'mulai_visitasi' => 'nullable|date',
            'akhir_visitasi' => 'nullable|date',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        PengaturanPeriode::create($validated);

        return back()->with('success', 'Pengaturan periode berhasil ditambahkan.');
    }

    public function update(Request $request, PengaturanPeriode $pengaturanPeriode): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_periode_id' => 'required|exists:tahun_periode,id',
            'lembaga_akreditasi_id' => 'required|exists:lembaga_akreditasi,id',
            'mulai_evaluasi_diri' => 'nullable|date',
            'akhir_evaluasi_diri' => 'nullable|date',
            'mulai_desk_eval' => 'nullable|date',
            'akhir_desk_eval' => 'nullable|date',
            'mulai_visitasi' => 'nullable|date',
            'akhir_visitasi' => 'nullable|date',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        $pengaturanPeriode->update($validated);

        return back()->with('success', 'Pengaturan periode berhasil diperbarui.');
    }

    public function destroy(PengaturanPeriode $pengaturanPeriode): RedirectResponse
    {
        $pengaturanPeriode->delete();

        return back()->with('success', 'Pengaturan periode berhasil dihapus.');
    }
}
