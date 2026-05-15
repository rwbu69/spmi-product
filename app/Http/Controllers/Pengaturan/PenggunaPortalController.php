<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\Auditee;
use App\Models\AuditeePusat;
use App\Models\Auditor;
use App\Models\UnitPenunjang;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class PenggunaPortalController extends Controller
{
    /**
     * Portal roles: Prodi (=Auditee), Auditor, Fakultas, Unit Penunjang
     */
    private const PORTAL_ROLES = ['Auditee', 'Auditor', 'Fakultas', 'Unit Penunjang'];

    public function index(Request $request): Response
    {
        $query = User::with('roles')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', self::PORTAL_ROLES));

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            });
        }

        if ($request->group) {
            $roleName = $request->group === 'Prodi' ? 'Auditee' : $request->group;
            $query->whereHas('roles', fn ($q) => $q->where('name', $roleName));
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pengaturan/PenggunaPortal/Index', [
            'data'             => $data,
            'filters'          => $request->only(['search', 'group']),
            'auditeeList'      => Auditee::orderBy('nama_auditee')->get(['id', 'nama_auditee']),
            'auditorList'      => Auditor::orderBy('nama')->get(['id', 'nama']),
            'fakultasList'     => AuditeePusat::orderBy('nama')->get(['id', 'nama']),
            'unitPenunjangList' => UnitPenunjang::orderBy('nama_unit')->get(['id', 'nama_unit']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'username'          => 'required|string|max:255|unique:users|regex:/^\S+$/',
            'password'          => 'required|string|min:8',
            'group'             => 'required|string|in:Prodi,Auditor,Fakultas,Unit Penunjang',
            'auditee_id'        => 'nullable|required_if:group,Prodi|exists:auditee,id',
            'auditor_id'        => 'nullable|required_if:group,Auditor|exists:auditor,id',
            'auditee_pusat_id'  => 'nullable|required_if:group,Fakultas|exists:auditee_pusat,id',
            'unit_penunjang_id' => 'nullable|required_if:group,Unit Penunjang|exists:unit_penunjang,id',
            'keterangan'        => 'nullable|string',
            'is_active'         => 'boolean',
        ]);

        $roleName = $validated['group'] === 'Prodi' ? 'Auditee' : $validated['group'];

        $user = User::create([
            'name'              => $validated['name'],
            'username'          => $validated['username'],
            'email'             => $validated['username'] . '@portal.local',
            'password'          => Hash::make($validated['password']),
            'auditee_id'        => $validated['auditee_id'] ?? null,
            'auditor_id'        => $validated['auditor_id'] ?? null,
            'auditee_pusat_id'  => $validated['auditee_pusat_id'] ?? null,
            'unit_penunjang_id' => $validated['unit_penunjang_id'] ?? null,
            'keterangan'        => $validated['keterangan'] ?? null,
            'is_active'         => $validated['is_active'] ?? true,
        ]);

        $user->syncRoles([$roleName]);

        return back()->with('success', 'Pengguna portal berhasil ditambahkan.');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'username'          => 'required|string|max:255|unique:users,username,' . $user->id . '|regex:/^\S+$/',
            'password'          => 'nullable|string|min:8',
            'group'             => 'required|string|in:Prodi,Auditor,Fakultas,Unit Penunjang',
            'auditee_id'        => 'nullable|required_if:group,Prodi|exists:auditee,id',
            'auditor_id'        => 'nullable|required_if:group,Auditor|exists:auditor,id',
            'auditee_pusat_id'  => 'nullable|required_if:group,Fakultas|exists:auditee_pusat,id',
            'unit_penunjang_id' => 'nullable|required_if:group,Unit Penunjang|exists:unit_penunjang,id',
            'keterangan'        => 'nullable|string',
            'is_active'         => 'boolean',
        ]);

        $roleName = $validated['group'] === 'Prodi' ? 'Auditee' : $validated['group'];

        $data = [
            'name'              => $validated['name'],
            'username'          => $validated['username'],
            'auditee_id'        => $validated['group'] === 'Prodi' ? ($validated['auditee_id'] ?? null) : null,
            'auditor_id'        => $validated['group'] === 'Auditor' ? ($validated['auditor_id'] ?? null) : null,
            'auditee_pusat_id'  => $validated['group'] === 'Fakultas' ? ($validated['auditee_pusat_id'] ?? null) : null,
            'unit_penunjang_id' => $validated['group'] === 'Unit Penunjang' ? ($validated['unit_penunjang_id'] ?? null) : null,
            'keterangan'        => $validated['keterangan'] ?? null,
            'is_active'         => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles([$roleName]);

        return back()->with('success', 'Pengguna portal berhasil diperbarui.');
    }

    public function destroy($id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus diri sendiri.');
        }

        $user->delete();
        return back()->with('success', 'Pengguna portal berhasil dihapus.');
    }
}
