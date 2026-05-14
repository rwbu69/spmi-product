<?php

namespace App\Http\Controllers\Pelaksanaan;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\EvaluasiDiri;
use App\Models\PengaturanPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EvaluasiDiriController extends Controller
{
    public function index(Request $request): Response
    {
        $isAuditee = auth()->user()?->hasAnyRole(['Auditee', 'Unit Penunjang']);

        $query = EvaluasiDiri::with(['auditee', 'pengaturanPeriode.tahunPeriode', 'pengaturanPeriode.lembagaAkreditasi']);

        if ($request->search) {
            $query->whereHas('auditee', function($q) use ($request) {
                $q->where('nama_auditee', 'like', "%{$request->search}%");
            });
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        $periodeList = PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(fn ($p) => [
            'id'    => $p->id,
            'label' => "{$p->lembagaAkreditasi->nama_lembaga} [{$p->tahunPeriode->tahun}]",
        ]);

        // Build period alert banners for Auditee (expired periods)
        $periodeAlerts = [];
        if ($isAuditee) {
            $allPeriode = PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get();
            foreach ($allPeriode as $p) {
                // Assume tanggal_mulai / tanggal_selesai fields exist, fallback gracefully
                $selesai = $p->tanggal_selesai ?? null;
                if ($selesai && now()->isAfter($selesai)) {
                    $hariLewat = now()->diffInDays($selesai);
                    $periodeAlerts[] = [
                        'label'      => "{$p->lembagaAkreditasi->nama_lembaga} [{$p->tahunPeriode->tahun}]",
                        'mulai'      => optional($p->tanggal_mulai)->format('d-m-Y') ?? '-',
                        'selesai'    => optional($selesai)->format('d-m-Y') ?? '-',
                        'hari_lewat' => $hariLewat,
                    ];
                }
            }
        }

        return Inertia::render('Pelaksanaan/EvaluasiDiri/Index', [
            'data'          => $data,
            'filters'       => $request->only(['search', 'periode_id']),
            'periodeList'   => $periodeList,
            'auditeeList'   => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
            'periodeAlerts' => $periodeAlerts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pengaturan_periode_id' => 'required|exists:pengaturan_periode,id',
            'auditee_id' => 'required|exists:auditee,id',
            'status' => 'required|in:Draft,Submitted,Approved',
        ]);

        EvaluasiDiri::updateOrCreate(
            ['pengaturan_periode_id' => $validated['pengaturan_periode_id'], 'auditee_id' => $validated['auditee_id']],
            ['status' => $validated['status']]
        );

        return back()->with('success', 'Evaluasi diri berhasil diinisialisasi.');
    }

    public function destroy(EvaluasiDiri $evaluasiDiri): RedirectResponse
    {
        $evaluasiDiri->delete();
        return back()->with('success', 'Evaluasi diri berhasil dihapus.');
    }
}
