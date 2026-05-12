<?php

namespace App\Http\Controllers\Referensi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referensi\AuditeeRequest;
use App\Models\Auditee;
use App\Models\AuditeePusat;
use App\Models\LembagaAkreditasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AuditeeController extends Controller
{
    public function index(Request $request): Response
    {
        $data = Auditee::with('auditeePusat:id,nama')
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('kode', 'like', "%{$request->search}%")
                    ->orWhere('nama_auditee', 'like', "%{$request->search}%");
            }))
            ->orderBy('kode')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Referensi/Auditee/Index', [
            'data'         => $data,
            'filters'      => $request->only('search'),
            'auditeePusat' => AuditeePusat::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(AuditeeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('sk_file')) {
            $data['sk_file_path'] = $request->file('sk_file')->store('auditee/sk', 'public');
        }
        unset($data['sk_file']);

        Auditee::create($data);

        return back()->with('success', 'Auditee berhasil ditambahkan.');
    }

    public function update(AuditeeRequest $request, Auditee $auditee): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('sk_file')) {
            if ($auditee->sk_file_path) {
                Storage::disk('public')->delete($auditee->sk_file_path);
            }
            $data['sk_file_path'] = $request->file('sk_file')->store('auditee/sk', 'public');
        }
        unset($data['sk_file']);

        $auditee->update($data);

        return back()->with('success', 'Auditee berhasil diperbarui.');
    }

    public function destroy(Auditee $auditee): RedirectResponse
    {
        if ($auditee->sk_file_path) {
            Storage::disk('public')->delete($auditee->sk_file_path);
        }

        $auditee->delete();

        return back()->with('success', 'Auditee berhasil dihapus.');
    }
}
