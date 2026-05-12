<?php

namespace App\Http\Controllers\Referensi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referensi\AuditeePusatRequest;
use App\Models\AuditeePusat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditeePusatController extends Controller
{
    public function index(Request $request): Response
    {
        $data = AuditeePusat::query()
            ->when($request->search, fn ($q) => $q->where('nama', 'like', "%{$request->search}%"))
            ->withCount('auditee')
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Referensi/AuditeePusat/Index', [
            'data'    => $data,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(AuditeePusatRequest $request): RedirectResponse
    {
        AuditeePusat::create($request->validated());

        return back()->with('success', 'Auditee pusat berhasil ditambahkan.');
    }

    public function update(AuditeePusatRequest $request, AuditeePusat $auditeePusat): RedirectResponse
    {
        $auditeePusat->update($request->validated());

        return back()->with('success', 'Auditee pusat berhasil diperbarui.');
    }

    public function destroy(AuditeePusat $auditeePusat): RedirectResponse
    {
        $auditeePusat->delete();

        return back()->with('success', 'Auditee pusat berhasil dihapus.');
    }
}
