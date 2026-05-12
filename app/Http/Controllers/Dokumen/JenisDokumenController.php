<?php

namespace App\Http\Controllers\Dokumen;

use App\Http\Controllers\Controller;
use App\Models\JenisDokumen;
use App\Models\KategoriDokumen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class JenisDokumenController extends Controller
{
    public function index(Request $request): Response
    {
        $data = JenisDokumen::with('kategoriDokumen:id,nama_kategori')
            ->when($request->search, fn ($q) => $q->where('nama_jenis', 'like', "%{$request->search}%"))
            ->orderBy('nama_jenis')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dokumen/JenisDokumen/Index', [
            'data'     => $data,
            'filters'  => $request->only('search'),
            'kategori' => KategoriDokumen::orderBy('nama_kategori')->get(['id', 'nama_kategori']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_jenis'          => ['required', 'string', 'max:255'],
            'kategori_dokumen_id' => ['required', 'exists:kategori_dokumen,id'],
        ], [], ['nama_jenis' => 'Nama Jenis', 'kategori_dokumen_id' => 'Kategori']);

        JenisDokumen::create($request->only('nama_jenis', 'kategori_dokumen_id'));

        return back()->with('success', 'Jenis dokumen berhasil ditambahkan.');
    }

    public function update(Request $request, JenisDokumen $jeni): RedirectResponse
    {
        $request->validate([
            'nama_jenis'          => ['required', 'string', 'max:255'],
            'kategori_dokumen_id' => ['required', 'exists:kategori_dokumen,id'],
        ], [], ['nama_jenis' => 'Nama Jenis', 'kategori_dokumen_id' => 'Kategori']);

        $jeni->update($request->only('nama_jenis', 'kategori_dokumen_id'));

        return back()->with('success', 'Jenis dokumen berhasil diperbarui.');
    }

    public function destroy(JenisDokumen $jeni): RedirectResponse
    {
        $jeni->delete();

        return back()->with('success', 'Jenis dokumen berhasil dihapus.');
    }
}
