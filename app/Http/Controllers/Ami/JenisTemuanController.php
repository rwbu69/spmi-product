<?php

namespace App\Http\Controllers\Ami;

use App\Http\Controllers\Controller;
use App\Models\JenisTemuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JenisTemuanController extends Controller
{
    public function index(Request $request): Response
    {
        $data = JenisTemuan::orderBy('nama')->get();

        return Inertia::render('Ami/JenisTemuan/Index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required|in:Positif,Negatif',
        ]);

        JenisTemuan::create($validated);

        return back()->with('success', 'Jenis temuan berhasil ditambahkan.');
    }

    public function update(Request $request, JenisTemuan $jenisTemuan): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required|in:Positif,Negatif',
        ]);

        $jenisTemuan->update($validated);

        return back()->with('success', 'Jenis temuan berhasil diperbarui.');
    }

    public function destroy(JenisTemuan $jenisTemuan): RedirectResponse
    {
        $jenisTemuan->delete();

        return back()->with('success', 'Jenis temuan berhasil dihapus.');
    }
}
