<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\AuditeePusat;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class PenggunaBackofficeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::with('roles')->withTrashed(false)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Admin'));

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            });
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        // Add computed 'unit' field based on entity relationships
        $data->getCollection()->transform(function ($user) {
            $unit = '-';
            if ($user->auditee_pusat_id) {
                $pusat = AuditeePusat::find($user->auditee_pusat_id);
                $unit = $pusat ? "[{$pusat->nama}]" : '-';
            }
            $user->unit_display = $unit;
            return $user;
        });

        return Inertia::render('Pengaturan/PenggunaBackoffice/Index', [
            'data' => $data,
            'filters' => $request->only(['search']),
            'auditeePusatList' => AuditeePusat::orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'username'         => 'required|string|max:255|unique:users|regex:/^\S+$/',
            'password'         => 'required|string|min:8',
            'auditee_pusat_id' => 'nullable|exists:auditee_pusat,id',
            'keterangan'       => 'nullable|string',
            'is_active'        => 'boolean',
        ]);

        $user = User::create([
            'name'             => $validated['name'],
            'username'         => $validated['username'],
            'email'            => $validated['username'] . '@system.local',
            'password'         => Hash::make($validated['password']),
            'auditee_pusat_id' => $validated['auditee_pusat_id'] ?? null,
            'keterangan'       => $validated['keterangan'] ?? null,
            'is_active'        => $validated['is_active'] ?? true,
        ]);

        $user->syncRoles(['Admin']);

        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'username'         => 'required|string|max:255|unique:users,username,' . $user->id . '|regex:/^\S+$/',
            'password'         => 'nullable|string|min:8',
            'auditee_pusat_id' => 'nullable|exists:auditee_pusat,id',
            'keterangan'       => 'nullable|string',
            'is_active'        => 'boolean',
        ]);

        $data = [
            'name'             => $validated['name'],
            'username'         => $validated['username'],
            'auditee_pusat_id' => $validated['auditee_pusat_id'] ?? null,
            'keterangan'       => $validated['keterangan'] ?? null,
            'is_active'        => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles(['Admin']);

        return back()->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus diri sendiri.');
        }

        $user->delete();
        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
