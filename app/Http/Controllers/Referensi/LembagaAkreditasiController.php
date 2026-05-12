<?php

namespace App\Http\Controllers\Referensi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referensi\LembagaAkreditasiRequest;
use App\Models\LembagaAkreditasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LembagaAkreditasiController extends Controller
{
    public function index(Request $request): Response
    {
        $data = LembagaAkreditasi::query()
            ->when($request->search, fn ($q) => $q->where('nama_lembaga', 'like', "%{$request->search}%"))
            ->orderBy('nama_lembaga')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Referensi/LembagaAkreditasi/Index', [
            'data'    => $data,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(LembagaAkreditasiRequest $request): RedirectResponse
    {
        LembagaAkreditasi::create($request->validated());

        return back()->with('success', 'Lembaga akreditasi berhasil ditambahkan.');
    }

    public function update(LembagaAkreditasiRequest $request, LembagaAkreditasi $lembagaAkreditasi): RedirectResponse
    {
        $lembagaAkreditasi->update($request->validated());

        return back()->with('success', 'Lembaga akreditasi berhasil diperbarui.');
    }

    public function destroy(LembagaAkreditasi $lembagaAkreditasi): RedirectResponse
    {
        $lembagaAkreditasi->delete();

        return back()->with('success', 'Lembaga akreditasi berhasil dihapus.');
    }
}
