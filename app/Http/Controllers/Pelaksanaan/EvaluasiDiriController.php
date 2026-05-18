<?php

namespace App\Http\Controllers\Pelaksanaan;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\EvaluasiDiri;
use App\Models\PengaturanPeriode;
use App\Models\TahunPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EvaluasiDiriController extends Controller
{
    public function index(Request $request): Response
    {
        $isAuditee = auth()->user()?->hasAnyRole(['Auditee', 'Unit Penunjang', 'Fakultas']);

        // Get active TahunPeriode
        $aktivPeriode = TahunPeriode::where('status', 'Aktif')->first();

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

        // Build the full periode list for Admin filter
        $periodeList = PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(fn ($p) => [
            'id'    => $p->id,
            'label' => "{$p->lembagaAkreditasi->nama_lembaga} [{$p->tahunPeriode->tahun}]",
        ]);

        // Build period alerts for Auditee/Unit Penunjang view
        // Show entries from PengaturanPeriode that belong to the active TahunPeriode
        // (these are the lembaga akreditasi that Admin has configured for this period)
        $periodeAlerts = [];
        $lembagaCount = 0;
        if ($isAuditee && $aktivPeriode) {
            $periodeEntries = PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])
                ->where('tahun_periode_id', $aktivPeriode->id)
                ->get();

            $lembagaCount = $periodeEntries->count();

            foreach ($periodeEntries as $p) {
                $mulai = $p->mulai_evaluasi_diri;
                $selesai = $p->akhir_evaluasi_diri;

                $alert = [
                    'label'      => "{$p->lembagaAkreditasi->nama_lembaga} [{$p->tahunPeriode->tahun}]",
                    'mulai'      => $mulai ? $mulai->format('d-m-Y') : '-',
                    'selesai'    => $selesai ? $selesai->format('d-m-Y') : '-',
                    'hari_lewat' => 0,
                    'is_active'  => false,
                    'is_expired' => false,
                ];

                if ($mulai && $selesai) {
                    if (now()->between($mulai, $selesai)) {
                        $alert['is_active'] = true;
                    } elseif (now()->isAfter($selesai)) {
                        $alert['is_expired'] = true;
                        $alert['hari_lewat'] = (int) now()->diffInDays($selesai);
                    }
                }

                $periodeAlerts[] = $alert;
            }
        }

        return Inertia::render('Pelaksanaan/EvaluasiDiri/Index', [
            'data'          => $data,
            'filters'       => $request->only(['search', 'periode_id']),
            'periodeList'   => $periodeList,
            'auditeeList'   => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
            'periodeAlerts' => $periodeAlerts,
            'lembagaCount'  => $lembagaCount,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Aksi ini tidak diizinkan.');
        }

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
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Aksi ini tidak diizinkan.');
        }

        $evaluasiDiri->delete();
        return back()->with('success', 'Evaluasi diri berhasil dihapus.');
    }
}
