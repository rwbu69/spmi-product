<?php

namespace App\Http\Controllers\Pengendalian;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\DraftLaporanRtm;
use App\Models\PengaturanPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DraftRtmController extends Controller
{
    public function index(Request $request): Response
    {
        $query = DraftLaporanRtm::with(['auditee', 'pengaturanPeriode.tahunPeriode', 'pengaturanPeriode.lembagaAkreditasi']);

        if ($request->search) {
            $query->where('nama_dokumen', 'like', "%{$request->search}%");
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pengendalian/DraftRtm/Index', [
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
        $request->validate([
            'pengaturan_periode_id' => 'required|exists:pengaturan_periode,id',
            'auditee_id' => 'required|exists:auditee,id',
            'nama_dokumen' => 'required|string',
            'file_path' => 'nullable|file|mimes:pdf|max:10240',
            'tanggal_dibuat' => 'required|date',
            'status' => 'required|in:Draft,Final',
        ]);

        $path = $request->hasFile('file_path') ? $request->file('file_path')->store('rtm/drafts', 'public') : null;

        DraftLaporanRtm::create([
            'pengaturan_periode_id' => $request->pengaturan_periode_id,
            'auditee_id' => $request->auditee_id,
            'nama_dokumen' => $request->nama_dokumen,
            'file_path' => $path,
            'tanggal_dibuat' => $request->tanggal_dibuat,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Draft RTM berhasil ditambahkan.');
    }

    public function update(Request $request, DraftLaporanRtm $draftRtm): RedirectResponse
    {
        $request->validate([
            'nama_dokumen' => 'required|string',
            'file_path' => 'nullable|file|mimes:pdf|max:10240',
            'tanggal_dibuat' => 'required|date',
            'status' => 'required|in:Draft,Final',
        ]);

        $data = $request->only(['nama_dokumen', 'tanggal_dibuat', 'status']);

        if ($request->hasFile('file_path')) {
            if ($draftRtm->file_path) {
                Storage::disk('public')->delete($draftRtm->file_path);
            }
            $data['file_path'] = $request->file('file_path')->store('rtm/drafts', 'public');
        }

        $draftRtm->update($data);

        return back()->with('success', 'Draft RTM berhasil diperbarui.');
    }

    public function destroy(DraftLaporanRtm $draftRtm): RedirectResponse
    {
        if ($draftRtm->file_path) {
            Storage::disk('public')->delete($draftRtm->file_path);
        }
        $draftRtm->delete();
        return back()->with('success', 'Draft RTM berhasil dihapus.');
    }
}
