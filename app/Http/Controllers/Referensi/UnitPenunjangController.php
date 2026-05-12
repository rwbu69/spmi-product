<?php

namespace App\Http\Controllers\Referensi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referensi\UnitPenunjangRequest;
use App\Models\AuditeePusat;
use App\Models\UnitPenunjang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitPenunjangController extends Controller
{
    public function index(Request $request): Response
    {
        $data = UnitPenunjang::with('auditeePusat:id,nama')
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('kode', 'like', "%{$request->search}%")
                    ->orWhere('nama_unit', 'like', "%{$request->search}%");
            }))
            ->orderBy('kode')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Referensi/UnitPenunjang/Index', [
            'data'         => $data,
            'filters'      => $request->only('search'),
            'auditeePusat' => AuditeePusat::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(UnitPenunjangRequest $request): RedirectResponse
    {
        UnitPenunjang::create($request->validated());

        return back()->with('success', 'Unit penunjang berhasil ditambahkan.');
    }

    public function update(UnitPenunjangRequest $request, UnitPenunjang $unitPenunjang): RedirectResponse
    {
        $unitPenunjang->update($request->validated());

        return back()->with('success', 'Unit penunjang berhasil diperbarui.');
    }

    public function destroy(UnitPenunjang $unitPenunjang): RedirectResponse
    {
        $unitPenunjang->delete();

        return back()->with('success', 'Unit penunjang berhasil dihapus.');
    }
}
