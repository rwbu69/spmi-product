<?php

namespace App\Http\Controllers\Ami;

use App\Http\Controllers\Controller;
use App\Models\JenisTemuan;
use App\Models\KategoriTemuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KategoriTemuanController extends Controller
{
    public function index(Request $request): Response
    {
        $data = KategoriTemuan::with('jenisTemuan')
            ->orderBy('nama_kategori')
            ->get();

        return Inertia::render('Ami/KategoriTemuan/Index', [
            'data' => $data,
            'jenisList' => JenisTemuan::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'jenis_temuan_id' => 'required|exists:jenis_temuan,id',
        ]);

        KategoriTemuan::create($validated);

        return back()->with('success', 'Kategori temuan berhasil ditambahkan.');
    }

    public function update(Request $request, KategoriTemuan $kategoriTemuan): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'jenis_temuan_id' => 'required|exists:jenis_temuan,id',
        ]);

        $kategoriTemuan->update($validated);

        return back()->with('success', 'Kategori temuan berhasil diperbarui.');
    }

    public function destroy(KategoriTemuan $kategoriTemuan): RedirectResponse
    {
        $kategoriTemuan->delete();

        return back()->with('success', 'Kategori temuan berhasil dihapus.');
    }
}
