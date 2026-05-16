<?php

namespace App\Http\Controllers\Dokumen;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\JenisDokumen;
use App\Models\KategoriDokumen;
use App\Models\ManajemenDokumen;
use App\Models\UnitPenunjang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ManajemenDokumenController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ManajemenDokumen::class);

        $user = $request->user();
        $query = ManajemenDokumen::with([
            'jenisDokumen.kategoriDokumen:id,nama_kategori',
            'jenisDokumen:id,nama_jenis,kategori_dokumen_id',
            'auditee:id,nama_auditee',
            'unitPenunjang:id,nama_unit',
            'user:id,name',
        ])
            ->when($request->search, fn ($q) => $q->where('nama_dokumen', 'like', "%{$request->search}%"))
            ->when($request->kategori_id, fn ($q) => $q->whereHas(
                'jenisDokumen',
                fn ($q2) => $q2->where('kategori_dokumen_id', $request->kategori_id)
            ))
            ->when($request->auditee_id, fn ($q) => $q->where('auditee_id', $request->auditee_id));

        if ($user) {
            $query->visibleTo($user);
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        // Build auditee list for filter: regular auditees only
        $auditeeList = Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']);

        // Build combined list for the form dropdown
        // Group 1: Auditee (regular programs)
        $auditeeOptions = Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee'])
            ->map(fn ($a) => [
                'value'   => 'auditee_' . $a->id,
                'label'   => $a->nama_auditee,
                'type'    => 'auditee',
                'id'      => $a->id,
                'auditee_id' => $a->id,
                'unit_penunjang_id' => null,
            ]);

        // Group 2: Unit Penunjang
        $unitOptions = UnitPenunjang::orderBy('nama_unit')->get(['id', 'nama_unit'])
            ->map(fn ($u) => [
                'value'   => 'unit_' . $u->id,
                'label'   => $u->nama_unit,
                'type'    => 'unit_penunjang',
                'id'      => $u->id,
                'auditee_id' => null,
                'unit_penunjang_id' => $u->id,
            ]);

        return Inertia::render('Dokumen/ManajemenDokumen/Index', [
            'data'           => $data,
            'filters'        => $request->only('search', 'kategori_id', 'auditee_id'),
            'kategoriList'   => KategoriDokumen::orderBy('nama_kategori')->get(['id', 'nama_kategori']),
            'jenisList'      => JenisDokumen::with('kategoriDokumen:id,nama_kategori')->orderBy('nama_jenis')->get(),
            'auditeeList'    => $auditeeList,
            'auditeeOptions' => $auditeeOptions,
            'unitOptions'    => $unitOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ManajemenDokumen::class);

        $request->validate([
            'jenis_dokumen_id'   => ['required', 'exists:jenis_dokumen,id'],
            'penerima_type'      => ['required', 'in:auditee,unit_penunjang,semua'],
            'auditee_id'         => ['nullable', 'required_if:penerima_type,auditee', 'exists:auditee,id'],
            'unit_penunjang_id'  => ['nullable', 'required_if:penerima_type,unit_penunjang', 'exists:unit_penunjang,id'],
            'nama_dokumen'       => ['required', 'string', 'max:255'],
            'tahun'              => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'file'               => ['required', 'file', 'mimes:pdf', 'max:7168'],
        ], [], [
            'jenis_dokumen_id'  => 'Jenis Dokumen',
            'auditee_id'        => 'Auditee',
            'unit_penunjang_id' => 'Unit Penunjang',
            'nama_dokumen'      => 'Nama Dokumen',
            'file'              => 'File (PDF)',
        ]);

        $path = $request->file('file')->store('dokumen');

        ManajemenDokumen::create([
            'jenis_dokumen_id'  => $request->jenis_dokumen_id,
            'auditee_id'        => $request->penerima_type === 'auditee' ? $request->auditee_id : null,
            'unit_penunjang_id' => $request->penerima_type === 'unit_penunjang' ? $request->unit_penunjang_id : null,
            'user_id'           => auth()->id(),
            'nama_dokumen'      => $request->nama_dokumen,
            'tahun'             => $request->tahun,
            'file_path'         => $path,
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function update(Request $request, ManajemenDokumen $manajemen): RedirectResponse
    {
        $this->authorize('update', $manajemen);

        $request->validate([
            'jenis_dokumen_id'  => ['required', 'exists:jenis_dokumen,id'],
            'penerima_type'     => ['required', 'in:auditee,unit_penunjang'],
            'auditee_id'        => ['nullable', 'required_if:penerima_type,auditee', 'exists:auditee,id'],
            'unit_penunjang_id' => ['nullable', 'required_if:penerima_type,unit_penunjang', 'exists:unit_penunjang,id'],
            'nama_dokumen'      => ['required', 'string', 'max:255'],
            'tahun'             => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'file'              => ['nullable', 'file', 'mimes:pdf', 'max:7168'],
        ]);

        $data = [
            'jenis_dokumen_id'  => $request->jenis_dokumen_id,
            'auditee_id'        => $request->penerima_type === 'auditee' ? $request->auditee_id : null,
            'unit_penunjang_id' => $request->penerima_type === 'unit_penunjang' ? $request->unit_penunjang_id : null,
            'nama_dokumen'      => $request->nama_dokumen,
            'tahun'             => $request->tahun,
        ];

        if ($request->hasFile('file')) {
            Storage::disk('local')->delete($manajemen->file_path);
            $data['file_path'] = $request->file('file')->store('dokumen');
        }

        $manajemen->update($data);

        return back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(ManajemenDokumen $manajemen): RedirectResponse
    {
        $this->authorize('delete', $manajemen);

        $manajemen->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function download(ManajemenDokumen $manajemen)
    {
        $this->authorize('view', $manajemen);

        $path = $manajemen->file_path;

        if (! Storage::disk('local')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $filename = preg_replace('/[^\w\-.]/', '_', $manajemen->nama_dokumen) . '.pdf';
        $fullPath = Storage::disk('local')->path($path);

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
