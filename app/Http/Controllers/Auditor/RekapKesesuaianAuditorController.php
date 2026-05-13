<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\LembagaAkreditasi;
use App\Models\TahunPeriode;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RekapKesesuaianAuditorController extends Controller
{
    public function index(Request $request): Response
    {
        $tahun     = $request->tahun ?? date('Y');
        $lembagaId = $request->lembaga_id;

        $query = Auditee::withCount([
            'daftarKesesuaian as jumlah_kesesuaian' => function ($q) use ($tahun, $lembagaId) {
                $q->whereHas('pengaturanPeriode', function ($q2) use ($tahun, $lembagaId) {
                    $q2->whereHas('tahunPeriode', fn ($q3) => $q3->where('tahun', $tahun));
                    if ($lembagaId) {
                        $q2->where('lembaga_akreditasi_id', $lembagaId);
                    }
                });
            },
        ])->orderBy('nama_auditee');

        $data = $query->paginate(10)->withQueryString();

        return Inertia::render('Auditor/RekapKesesuaian/Index', [
            'data'         => $data,
            'filters'      => $request->only(['tahun', 'lembaga_id']),
            'tahunList'    => TahunPeriode::orderByDesc('tahun')->get(['id', 'tahun']),
            'lembagaList'  => LembagaAkreditasi::orderBy('nama_lembaga')->get(['id', 'nama_lembaga']),
            'currentTahun' => $tahun,
        ]);
    }
}
