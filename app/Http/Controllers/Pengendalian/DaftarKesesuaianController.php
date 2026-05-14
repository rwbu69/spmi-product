<?php

namespace App\Http\Controllers\Pengendalian;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\DaftarKesesuaian;
use App\Models\LembagaAkreditasi;
use App\Models\PengaturanPeriode;
use App\Models\StandarMutu;
use App\Models\TahunPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class DaftarKesesuaianController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = DaftarKesesuaian::with([
            'auditee:id,nama_auditee',
            'standarMutu:id,kode,nama_standar',
            'pengaturanPeriode.tahunPeriode:id,tahun',
            'pengaturanPeriode.lembagaAkreditasi:id,nama_lembaga',
        ]);

        if ($request->search) {
            $query->where('deskripsi', 'like', "%{$request->search}%");
        }

        if ($request->periode_id) {
            $query->where('pengaturan_periode_id', $request->periode_id);
        }

        // Filter by tahun
        if ($request->tahun_id) {
            $query->whereHas('pengaturanPeriode', fn ($q) => $q->where('tahun_periode_id', $request->tahun_id));
        }

        // Filter by lembaga
        if ($request->lembaga_id) {
            $query->whereHas('pengaturanPeriode', fn ($q) => $q->where('lembaga_akreditasi_id', $request->lembaga_id));
        }

        return $query;
    }

    public function index(Request $request): Response
    {
        $data = $this->buildQuery($request)->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pengendalian/Kesesuaian/Index', [
            'data'        => $data,
            'filters'     => $request->only(['search', 'periode_id', 'tahun_id', 'lembaga_id']),
            'periodeList' => PengaturanPeriode::with(['tahunPeriode', 'lembagaAkreditasi'])->get()->map(fn ($p) => [
                'id'    => $p->id,
                'label' => "Periode {$p->tahunPeriode->tahun} - {$p->lembagaAkreditasi->nama_lembaga}",
            ]),
            'tahunList'   => TahunPeriode::orderByDesc('tahun')->get(['id', 'tahun']),
            'lembagaList' => LembagaAkreditasi::orderBy('nama_lembaga')->get(['id', 'nama_lembaga']),
            'auditeeList' => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
            'standarList' => StandarMutu::whereNull('parent_id')->get(['id', 'kode', 'nama_standar']),
        ]);
    }

    public function export(Request $request): HttpResponse
    {
        $items = $this->buildQuery($request)->latest()->get();

        $filename = 'daftar_kesesuaian_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, ['No', 'Auditee', 'Standar Mutu', 'Kode', 'Temuan Positif', 'Deskripsi', 'Peningkatan', 'Nilai Mutu', 'Periode', 'Lembaga']);

            foreach ($items as $i => $item) {
                fputcsv($handle, [
                    $i + 1,
                    $item->auditee->nama_auditee ?? '-',
                    $item->standarMutu->nama_standar ?? '-',
                    $item->standarMutu->kode ?? '-',
                    $item->temuan_positif ?? '',
                    $item->deskripsi ?? '',
                    $item->peningkatan ?? '',
                    $item->nilai_mutu ?? 0,
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
            'auditee_id'           => 'required|exists:auditee,id',
            'standar_mutu_id'      => 'required|exists:standar_mutu,id',
            'pengaturan_periode_id' => 'required|exists:pengaturan_periode,id',
            'deskripsi'            => 'nullable|string',
            'peningkatan'          => 'nullable|string',
            'nilai_mutu'           => 'nullable|numeric|min:0|max:100',
            'temuan_positif'       => 'nullable|string',
        ]);

        DaftarKesesuaian::create($validated);

        return back()->with('success', 'Daftar kesesuaian berhasil ditambahkan.');
    }

    public function update(Request $request, DaftarKesesuaian $kesesuaian): RedirectResponse
    {
        $validated = $request->validate([
            'deskripsi'      => 'nullable|string',
            'peningkatan'    => 'nullable|string',
            'nilai_mutu'     => 'nullable|numeric|min:0|max:100',
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
