<?php

namespace App\Http\Controllers\Penetapan;

use App\Http\Controllers\Controller;
use App\Models\Indikator;
use App\Models\StandarMutu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IndikatorController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'standar_mutu_id' => 'required|exists:standar_mutu,id',
            'deskripsi' => 'required|string',
            'bobot' => 'required|numeric|min:0|max:100',
        ]);

        Indikator::create($validated);

        return back()->with('success', 'Indikator berhasil ditambahkan.');
    }

    public function update(Request $request, Indikator $indikator): RedirectResponse
    {
        $validated = $request->validate([
            'deskripsi' => 'required|string',
            'bobot' => 'required|numeric|min:0|max:100',
        ]);

        $indikator->update($validated);

        return back()->with('success', 'Indikator berhasil diperbarui.');
    }

    public function destroy(Indikator $indikator): RedirectResponse
    {
        $indikator->delete();

        return back()->with('success', 'Indikator berhasil dihapus.');
    }
}
