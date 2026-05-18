<?php

namespace App\Http\Controllers\Referensi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referensi\TahunPeriodeRequest;
use App\Models\TahunPeriode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TahunPeriodeController extends Controller
{
    public function index(Request $request): Response
    {
        $data = TahunPeriode::query()
            ->when($request->search, fn ($q) => $q->where('tahun', 'like', "%{$request->search}%"))
            ->orderByDesc('tahun')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Referensi/TahunPeriode/Index', [
            'data'    => $data,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(TahunPeriodeRequest $request): RedirectResponse
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            // Automatically turn off all existing active periods
            TahunPeriode::where('status', 'Aktif')->update(['status' => 'Tidak Aktif']);
            
            // Create the new period as active
            TahunPeriode::create(array_merge($request->validated(), ['status' => 'Aktif']));
        });

        return back()->with('success', 'Tahun periode berhasil ditambahkan.');
    }

    public function update(TahunPeriodeRequest $request, TahunPeriode $tahunPeriode): RedirectResponse
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $tahunPeriode) {
            $validated = $request->validated();
            
            // If the period status is updated to active, deactivate all other periods
            if (($validated['status'] ?? null) === 'Aktif') {
                TahunPeriode::where('id', '!=', $tahunPeriode->id)
                    ->where('status', 'Aktif')
                    ->update(['status' => 'Tidak Aktif']);
            }
            
            $tahunPeriode->update($validated);
        });

        return back()->with('success', 'Tahun periode berhasil diperbarui.');
    }

    public function destroy(TahunPeriode $tahunPeriode): RedirectResponse
    {
        if ($tahunPeriode->status === 'Aktif') {
            return back()->with('error', 'Tahun periode dengan status Aktif tidak dapat dihapus.');
        }

        $tahunPeriode->delete();

        return back()->with('success', 'Tahun periode berhasil dihapus.');
    }
}
