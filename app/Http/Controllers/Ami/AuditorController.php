<?php

namespace App\Http\Controllers\Ami;

use App\Http\Controllers\Controller;
use App\Models\Auditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AuditorController extends Controller
{
    public function index(Request $request): Response
    {
        $data = Auditor::query()
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('nama', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Ami/Auditor/Index', [
            'data'    => $data,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'email'    => ['nullable', 'email', 'max:255', Rule::unique('auditor', 'email')],
            'keahlian' => ['nullable', 'string', 'max:255'],
            'no_hp'    => ['nullable', 'string', 'max:20'],
            'keterangan' => ['nullable', 'string'],
        ], [], ['nama' => 'Nama', 'email' => 'Email']);

        Auditor::create($request->only('nama', 'email', 'keahlian', 'no_hp', 'keterangan'));

        return back()->with('success', 'Auditor berhasil ditambahkan.');
    }

    public function update(Request $request, Auditor $auditor): RedirectResponse
    {
        $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'email'    => ['nullable', 'email', 'max:255', Rule::unique('auditor', 'email')->ignore($auditor->id)],
            'keahlian' => ['nullable', 'string', 'max:255'],
            'no_hp'    => ['nullable', 'string', 'max:20'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $auditor->update($request->only('nama', 'email', 'keahlian', 'no_hp', 'keterangan'));

        return back()->with('success', 'Auditor berhasil diperbarui.');
    }

    public function destroy(Auditor $auditor): RedirectResponse
    {
        $auditor->delete();

        return back()->with('success', 'Auditor berhasil dihapus.');
    }
}
