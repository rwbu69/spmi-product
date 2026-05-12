<?php

namespace App\Http\Controllers\Pengendalian;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\DaftarTemuanPp;
use App\Models\PengaturanPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DaftarTemuanController extends Controller
{
    public function index(Request $request): Response
    {
        $query = DaftarTemuanPp::with(['auditee', 'pengaturanPeriode.tahunPeriode', 'pengaturanPeriode.lembagaAkreditasi']);

        if ($request->search) {
            $query->where('uraian_temuan', 'like', "%{$request->search}%");
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pengendalian/DaftarTemuan/Index', [
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
            'uraian_temuan' => 'required|string',
            'jenis' => 'required|string',
            'status' => 'required|in:Open,In Progress,Closed',
        ]);

        DaftarTemuanPp::create($validated);

        return back()->with('success', 'Daftar temuan berhasil ditambahkan.');
    }

    public function update(Request $request, DaftarTemuanPp $daftarTemuan): RedirectResponse
    {
        $validated = $request->validate([
            'uraian_temuan' => 'required|string',
            'jenis' => 'required|string',
            'status' => 'required|in:Open,In Progress,Closed',
        ]);

        $daftarTemuan->update($validated);

        return back()->with('success', 'Daftar temuan berhasil diperbarui.');
    }

    public function destroy(DaftarTemuanPp $daftarTemuan): RedirectResponse
    {
        $daftarTemuan->delete();
        return back()->with('success', 'Daftar temuan berhasil dihapus.');
    }
}
