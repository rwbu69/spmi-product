<?php

namespace App\Http\Controllers\Pengendalian;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\DaftarTemuanPp;
use App\Models\LembagaAkreditasi;
use App\Models\PengaturanPeriode;
use App\Models\TahunPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class DaftarTemuanController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = DaftarTemuanPp::with([
            'auditee:id,nama_auditee',
            'pengaturanPeriode.tahunPeriode:id,tahun',
            'pengaturanPeriode.lembagaAkreditasi:id,nama_lembaga',
            'temuan.deskEvaluation.indikator.standarMutu:id,kode,nama_standar',
            'temuan:id,desk_evaluation_id,deskripsi,rekomendasi',
            'rencanaTindakLanjut',
        ]);

        // Scope to current user's auditee for Auditee/Unit Penunjang
        $user = auth()->user();
        if ($user && $user->hasAnyRole(['Auditee', 'Unit Penunjang'])) {
            $auditeeId = $user->auditee_id;
            $unitId = $user->unit_penunjang_id;

            if ($auditeeId) {
                $query->where('auditee_id', $auditeeId);
            }
        }

        if ($request->search) {
            $query->where('uraian_temuan', 'like', "%{$request->search}%");
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        if ($request->tahun_id) {
            $query->whereHas('pengaturanPeriode', fn ($q) => $q->where('tahun_periode_id', $request->tahun_id));
        }

        if ($request->lembaga_id) {
            $query->whereHas('pengaturanPeriode', fn ($q) => $q->where('lembaga_akreditasi_id', $request->lembaga_id));
        }

        return $query;
    }

    public function index(Request $request): Response
    {
        $perPage = min((int) ($request->per_page ?? 10), 100);
        $data = $this->buildQuery($request)->latest()->paginate($perPage)->withQueryString();

        return Inertia::render('Pengendalian/DaftarTemuan/Index', [
            'data'        => $data,
            'filters'     => $request->only(['search', 'periode_id', 'tahun_id', 'lembaga_id', 'per_page']),
            'periodeList' => PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(fn ($p) => [
                'id'    => $p->id,
                'label' => "Periode {$p->tahunPeriode->tahun} - {$p->lembagaAkreditasi->nama_lembaga}",
            ]),
            'tahunList'   => TahunPeriode::orderByDesc('tahun')->get(['id', 'tahun']),
            'lembagaList' => LembagaAkreditasi::orderBy('nama_lembaga')->get(['id', 'nama_lembaga']),
            'auditeeList' => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
        ]);
    }

    public function export(Request $request)
    {
        $items = $this->buildQuery($request)->latest()->get();

        $data = [];
        foreach ($items as $i => $item) {
            $standar = $item->temuan?->deskEvaluation?->indikator?->standarMutu;
            $deskEval = $item->temuan?->deskEvaluation;
            $rtl = $item->rencanaTindakLanjut->pluck('uraian_rtm')->join('; ');

            $data[] = [
                $i + 1,
                $standar ? "[{$standar->kode}] {$standar->nama_standar}" : '-',
                $item->temuan?->deskripsi ?? '-',
                $deskEval?->nilai ?? '-',
                $item->uraian_temuan,
                $rtl ?: '-',
                $item->status,
                $item->auditee->nama_auditee ?? '-',
                $item->pengaturanPeriode->tahunPeriode->tahun ?? '-',
                $item->pengaturanPeriode->lembagaAkreditasi->nama_lembaga ?? '-',
            ];
        }

        $filename = 'daftar_temuan_' . now()->format('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DaftarTemuanExport($data), $filename);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Aksi ini tidak diizinkan.');
        }

        $validated = $request->validate([
            'auditee_id'            => 'required|exists:auditee,id',
            'pengaturan_periode_id'  => 'required|exists:pengaturan_periode,id',
            'uraian_temuan'         => 'required|string',
            'jenis'                 => 'required|string',
            'status'                => 'required|in:Open,In Progress,Closed',
        ]);

        DaftarTemuanPp::create($validated);

        return back()->with('success', 'Daftar temuan berhasil ditambahkan.');
    }

    public function update(Request $request, DaftarTemuanPp $daftarTemuan): RedirectResponse
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Aksi ini tidak diizinkan.');
        }

        $validated = $request->validate([
            'uraian_temuan' => 'required|string',
            'jenis'         => 'required|string',
            'status'        => 'required|in:Open,In Progress,Closed',
        ]);

        $daftarTemuan->update($validated);

        return back()->with('success', 'Daftar temuan berhasil diperbarui.');
    }

    public function destroy(DaftarTemuanPp $daftarTemuan): RedirectResponse
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Aksi ini tidak diizinkan.');
        }

        $daftarTemuan->delete();
        return back()->with('success', 'Daftar temuan berhasil dihapus.');
    }
}
