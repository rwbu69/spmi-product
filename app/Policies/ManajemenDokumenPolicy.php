<?php

namespace App\Policies;

use App\Models\ManajemenDokumen;
use App\Models\User;

class ManajemenDokumenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Fakultas', 'Auditee', 'Unit Penunjang']);
    }

    public function view(User $user, ManajemenDokumen $manajemenDokumen): bool
    {
        if ($user->hasAnyRole(['Admin', 'Fakultas'])) {
            return true;
        }

        if ($manajemenDokumen->user_id === $user->id) {
            return true;
        }

        if ($manajemenDokumen->auditee_id && $user->auditee_id === $manajemenDokumen->auditee_id) {
            return true;
        }

        if ($manajemenDokumen->unit_penunjang_id && $user->unit_penunjang_id === $manajemenDokumen->unit_penunjang_id) {
            return true;
        }

        return $manajemenDokumen->auditee_id === null && $manajemenDokumen->unit_penunjang_id === null;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function update(User $user, ManajemenDokumen $manajemenDokumen): bool
    {
        return $user->hasRole('Admin');
    }

    public function delete(User $user, ManajemenDokumen $manajemenDokumen): bool
    {
        return $user->hasRole('Admin');
    }
}
