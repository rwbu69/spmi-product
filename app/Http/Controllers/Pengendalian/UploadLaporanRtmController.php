<?php

namespace App\Http\Controllers\Pengendalian;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\TahunPeriode;
use App\Models\UploadLaporanRtm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class UploadLaporanRtmController extends Controller
{
    public function index(Request $request): Response
    {
        $query = UploadLaporanRtm::with(['auditee', 'tahunPeriode']);

        if ($request->search) {
            $query->where('nama_dokumen', 'like', "%{$request->search}%");
        }

        if ($request->tahun_id) {
            $query->where('tahun_periode_id', $request->tahun_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pengendalian/UploadRtm/Index', [
            'data' => $data,
            'filters' => $request->only(['search', 'tahun_id']),
            'tahunList' => TahunPeriode::orderByDesc('tahun')->get(),
            'auditeeList' => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'auditee_id' => 'required|exists:auditee,id',
            'tahun_periode_id' => 'required|exists:tahun_periode,id',
            'nama_dokumen' => 'required|string',
            'file_path' => 'required|file|mimes:pdf|max:10240',
        ]);

        $path = $request->file('file_path')->store('rtm/final', 'public');

        UploadLaporanRtm::create([
            'auditee_id' => $request->auditee_id,
            'tahun_periode_id' => $request->tahun_periode_id,
            'nama_dokumen' => $request->nama_dokumen,
            'file_path' => $path,
            'tanggal_upload' => now(),
            'status_download' => 'Tersedia',
        ]);

        return back()->with('success', 'Laporan RTM berhasil diunggah.');
    }

    public function destroy(UploadLaporanRtm $uploadRtm): RedirectResponse
    {
        Storage::disk('public')->delete($uploadRtm->file_path);
        $uploadRtm->delete();
        return back()->with('success', 'Laporan RTM berhasil dihapus.');
    }
}
