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
        ]);

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
        $data = $this->buildQuery($request)->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pengendalian/DaftarTemuan/Index', [
            'data'        => $data,
            'filters'     => $request->only(['search', 'periode_id', 'tahun_id', 'lembaga_id']),
            'periodeList' => PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(fn ($p) => [
                'id'    => $p->id,
                'label' => "Periode {$p->tahunPeriode->tahun} - {$p->lembagaAkreditasi->nama_lembaga}",
            ]),
            'tahunList'   => TahunPeriode::orderByDesc('tahun')->get(['id', 'tahun']),
            'lembagaList' => LembagaAkreditasi::orderBy('nama_lembaga')->get(['id', 'nama_lembaga']),
            'auditeeList' => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
        ]);
    }

    public function export(Request $request): HttpResponse
    {
        $items = $this->buildQuery($request)->latest()->get();

        $filename = 'daftar_temuan_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, ['No', 'Auditee', 'Uraian Temuan', 'Jenis', 'Status', 'Periode', 'Lembaga']);

            foreach ($items as $i => $item) {
                fputcsv($handle, [
                    $i + 1,
                    $item->auditee->nama_auditee ?? '-',
                    $item->uraian_temuan,
                    $item->jenis,
                    $item->status,
                    $item->pengaturanPeriode->tahunPeriode->tahun ?? '-',
                    $item->pengaturanPeriode->lembagaAkreditasi->nama_lembaga ?? '-',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request): RedirectResponse
    {
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
        $daftarTemuan->delete();
        return back()->with('success', 'Daftar temuan berhasil dihapus.');
    }
}
