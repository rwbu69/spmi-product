<?php

namespace App\Http\Controllers\Dokumen;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\JenisDokumen;
use App\Models\KategoriDokumen;
use App\Models\ManajemenDokumen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ManajemenDokumenController extends Controller
{
    public function index(Request $request): Response
    {
        $data = ManajemenDokumen::with([
            'jenisDokumen.kategoriDokumen:id,nama_kategori',
            'jenisDokumen:id,nama_jenis,kategori_dokumen_id',
            'auditee:id,nama_auditee',
            'user:id,name',
        ])
            ->when($request->search, fn ($q) => $q->where('nama_dokumen', 'like', "%{$request->search}%"))
            ->when($request->kategori_id, fn ($q) => $q->whereHas(
                'jenisDokumen',
                fn ($q2) => $q2->where('kategori_dokumen_id', $request->kategori_id)
            ))
            ->when($request->auditee_id, fn ($q) => $q->where('auditee_id', $request->auditee_id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dokumen/ManajemenDokumen/Index', [
            'data'         => $data,
            'filters'      => $request->only('search', 'kategori_id', 'auditee_id'),
            'kategoriList' => KategoriDokumen::orderBy('nama_kategori')->get(['id', 'nama_kategori']),
            'jenisList'    => JenisDokumen::with('kategoriDokumen:id,nama_kategori')->orderBy('nama_jenis')->get(),
            'auditeeList'  => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'jenis_dokumen_id' => ['required', 'exists:jenis_dokumen,id'],
            'auditee_id'       => ['required', 'exists:auditee,id'],
            'nama_dokumen'     => ['required', 'string', 'max:255'],
            'tahun'            => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'file'             => ['required', 'file', 'mimes:pdf', 'max:7168'],
        ], [], ['jenis_dokumen_id' => 'Jenis Dokumen', 'auditee_id' => 'Auditee', 'nama_dokumen' => 'Nama Dokumen', 'file' => 'File (PDF)']);

        $path = $request->file('file')->store('dokumen', 'public');

        ManajemenDokumen::create([
            'jenis_dokumen_id' => $request->jenis_dokumen_id,
            'auditee_id'       => $request->auditee_id,
            'user_id'          => auth()->id(),
            'nama_dokumen'     => $request->nama_dokumen,
            'tahun'            => $request->tahun,
            'file_path'        => $path,
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function update(Request $request, ManajemenDokumen $manajemen): RedirectResponse
    {
        $request->validate([
            'jenis_dokumen_id' => ['required', 'exists:jenis_dokumen,id'],
            'auditee_id'       => ['required', 'exists:auditee,id'],
            'nama_dokumen'     => ['required', 'string', 'max:255'],
            'tahun'            => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'file'             => ['nullable', 'file', 'mimes:pdf', 'max:7168'],
        ]);

        $data = $request->only('jenis_dokumen_id', 'auditee_id', 'nama_dokumen', 'tahun');

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($manajemen->file_path);
            $data['file_path'] = $request->file('file')->store('dokumen', 'public');
        }

        $manajemen->update($data);

        return back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(ManajemenDokumen $manajemen): RedirectResponse
    {
        Storage::disk('public')->delete($manajemen->file_path);
        $manajemen->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
