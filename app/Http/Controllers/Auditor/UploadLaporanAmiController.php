<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\LaporanAmi;
use App\Models\Auditee;
use App\Models\PengaturanPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class UploadLaporanAmiController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', LaporanAmi::class);

        $query = LaporanAmi::with(['auditee', 'pengaturanPeriode.tahunPeriode', 'pengaturanPeriode.lembagaAkreditasi']);

        if ($request->auditee_id) {
            $query->where('auditee_id', $request->auditee_id);
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Auditor/UploadLaporanAmi/Index', [
            'data'        => $data,
            'filters'     => $request->only(['auditee_id', 'periode_id']),
            'auditeeList' => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
            'periodeList' => PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(fn ($p) => [
                'id'    => $p->id,
                'label' => "Periode {$p->tahunPeriode->tahun} - {$p->lembagaAkreditasi->nama_lembaga}",
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', LaporanAmi::class);

        $request->validate([
            'pengaturan_periode_id' => 'required|exists:pengaturan_periode,id',
            'auditee_id'            => 'required|exists:auditee,id',
            'file_laporan'          => 'required|file|mimes:pdf|max:10240',
            'tanggal_laporan'       => 'required|date',
            'status'                => 'required|in:Draft,Final',
        ]);

        $path = $request->file('file_laporan')->store('laporan-ami', 'public');

        LaporanAmi::create([
            'pengaturan_periode_id' => $request->pengaturan_periode_id,
            'auditee_id'            => $request->auditee_id,
            'file_laporan'          => $path,
            'tanggal_laporan'       => $request->tanggal_laporan,
            'status'                => $request->status,
        ]);

        return back()->with('success', 'Laporan AMI berhasil diunggah.');
    }

    public function destroy(LaporanAmi $laporanAmi): RedirectResponse
    {
        $this->authorize('delete', $laporanAmi);

        Storage::disk('public')->delete($laporanAmi->file_laporan);
        $laporanAmi->delete();

        return back()->with('success', 'Laporan AMI berhasil dihapus.');
    }
}
