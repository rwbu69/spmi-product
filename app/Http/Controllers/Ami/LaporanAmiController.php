<?php

namespace App\Http\Controllers\Ami;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\LaporanAmi;
use App\Models\PengaturanPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LaporanAmiController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', LaporanAmi::class);

        $query = LaporanAmi::with(['auditee', 'pengaturanPeriode.tahunPeriode', 'pengaturanPeriode.lembagaAkreditasi']);

        if ($request->user()) {
            $query->visibleTo($request->user());
        }

        if ($request->search) {
            $query->whereHas('auditee', function($q) use ($request) {
                $q->where('nama_auditee', 'like', "%{$request->search}%");
            });
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        // For Auditee role: pass the current auditee record for the header info
        $currentAuditee = null;
        if ($request->user()?->hasAnyRole(['Auditee', 'Unit Penunjang']) && $request->user()?->auditee_id) {
            $currentAuditee = Auditee::where('id', $request->user()->auditee_id)->first(['id', 'nama_auditee']);
        }

        return Inertia::render('Ami/LaporanAmi/Index', [
            'data'           => $data,
            'filters'        => $request->only(['search', 'periode_id']),
            'periodeList'    => PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(fn ($p) => [
                'id'    => $p->id,
                'label' => "Periode {$p->tahunPeriode->tahun} - {$p->lembagaAkreditasi->nama_lembaga}",
            ]),
            'auditeeList'    => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
            'currentAuditee' => $currentAuditee,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', LaporanAmi::class);

        $request->validate([
            'pengaturan_periode_id' => 'required|exists:pengaturan_periode,id',
            'auditee_id' => 'required|exists:auditee,id',
            'file_laporan' => 'required|file|mimes:pdf|max:10240',
            'tanggal_laporan' => 'required|date',
            'status' => 'required|in:Draft,Final',
        ]);

        $path = $request->file('file_laporan')->store('laporan-ami', 'public');

        LaporanAmi::create([
            'pengaturan_periode_id' => $request->pengaturan_periode_id,
            'auditee_id' => $request->auditee_id,
            'file_laporan' => $path,
            'tanggal_laporan' => $request->tanggal_laporan,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Laporan AMI berhasil diunggah.');
    }

    public function update(Request $request, LaporanAmi $laporanAmi): RedirectResponse
    {
        $this->authorize('update', $laporanAmi);

        $request->validate([
            'file_laporan' => 'nullable|file|mimes:pdf|max:10240',
            'tanggal_laporan' => 'required|date',
            'status' => 'required|in:Draft,Final',
        ]);

        $data = $request->only(['tanggal_laporan', 'status']);

        if ($request->hasFile('file_laporan')) {
            Storage::disk('public')->delete($laporanAmi->file_laporan);
            $data['file_laporan'] = $request->file('file_laporan')->store('laporan-ami', 'public');
        }

        $laporanAmi->update($data);

        return back()->with('success', 'Laporan AMI berhasil diperbarui.');
    }

    public function destroy(LaporanAmi $laporanAmi): RedirectResponse
    {
        $this->authorize('delete', $laporanAmi);

        Storage::disk('public')->delete($laporanAmi->file_laporan);
        $laporanAmi->delete();

        return back()->with('success', 'Laporan AMI berhasil dihapus.');
    }

    public function download(LaporanAmi $laporanAmi)
    {
        $this->authorize('view', $laporanAmi);

        $fullPath = Storage::disk('public')->path($laporanAmi->file_laporan);

        if (! file_exists($fullPath)) {
            abort(404, 'File tidak ditemukan.');
        }

        $filename = 'Laporan_AMI_' . preg_replace('/[^\w\-.]/', '_', $laporanAmi->auditee->nama_auditee) . '_' . $laporanAmi->pengaturanPeriode->tahunPeriode->tahun . '.pdf';

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
