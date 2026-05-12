<?php

namespace App\Http\Controllers\Pengendalian;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\DaftarKesesuaian;
use App\Models\PengaturanPeriode;
use App\Models\StandarMutu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DaftarKesesuaianController extends Controller
{
    public function index(Request $request): Response
    {
        $query = DaftarKesesuaian::with(['auditee', 'standarMutu', 'pengaturanPeriode.tahunPeriode', 'pengaturanPeriode.lembagaAkreditasi']);

        if ($request->search) {
            $query->where('deskripsi', 'like', "%{$request->search}%");
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pengendalian/Kesesuaian/Index', [
            'data' => $data,
            'filters' => $request->only(['search', 'periode_id']),
            'periodeList' => PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(function($p) {
                return [
                    'id' => $p->id,
                    'label' => "Periode {$p->tahunPeriode->tahun} - {$p->lembagaAkreditasi->nama_lembaga}"
                ];
            }),
            'auditeeList' => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
            'standarList' => StandarMutu::whereNull('parent_id')->get(['id', 'kode', 'nama_standar']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auditee_id' => 'required|exists:auditee,id',
            'standar_mutu_id' => 'required|exists:standar_mutu,id',
            'pengaturan_periode_id' => 'required|exists:pengaturan_periode,id',
            'deskripsi' => 'nullable|string',
            'peningkatan' => 'nullable|string',
            'nilai_mutu' => 'nullable|numeric|min:0|max:100',
            'temuan_positif' => 'nullable|string',
        ]);

        DaftarKesesuaian::create($validated);

        return back()->with('success', 'Daftar kesesuaian berhasil ditambahkan.');
    }

    public function update(Request $request, DaftarKesesuaian $kesesuaian): RedirectResponse
    {
        $validated = $request->validate([
            'deskripsi' => 'nullable|string',
            'peningkatan' => 'nullable|string',
            'nilai_mutu' => 'nullable|numeric|min:0|max:100',
            'temuan_positif' => 'nullable|string',
        ]);

        $kesesuaian->update($validated);

        return back()->with('success', 'Daftar kesesuaian berhasil diperbarui.');
    }

    public function destroy(DaftarKesesuaian $kesesuaian): RedirectResponse
    {
        $kesesuaian->delete();
        return back()->with('success', 'Daftar kesesuaian berhasil dihapus.');
    }
}
