<?php

namespace App\Http\Controllers\Dokumen;

use App\Http\Controllers\Controller;
use App\Models\KategoriDokumen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class KategoriDokumenController extends Controller
{
    public function index(Request $request): Response
    {
        $data = KategoriDokumen::query()
            ->when($request->search, fn ($q) => $q->where('nama_kategori', 'like', "%{$request->search}%"))
            ->withCount('jenisDokumen')
            ->orderBy('nama_kategori')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dokumen/KategoriDokumen/Index', [
            'data'    => $data,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', Rule::unique('kategori_dokumen', 'nama_kategori')],
        ], [], ['nama_kategori' => 'Nama Kategori']);

        KategoriDokumen::create($request->only('nama_kategori'));

        return back()->with('success', 'Kategori dokumen berhasil ditambahkan.');
    }

    public function update(Request $request, KategoriDokumen $kategori): RedirectResponse
    {
        $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', Rule::unique('kategori_dokumen', 'nama_kategori')->ignore($kategori->id)],
        ], [], ['nama_kategori' => 'Nama Kategori']);

        $kategori->update($request->only('nama_kategori'));

        return back()->with('success', 'Kategori dokumen berhasil diperbarui.');
    }

    public function destroy(KategoriDokumen $kategori): RedirectResponse
    {
        $kategori->delete();

        return back()->with('success', 'Kategori dokumen berhasil dihapus.');
    }
}
