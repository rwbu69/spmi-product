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
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Fakultas']);
    }

    public function update(User $user, ManajemenDokumen $manajemenDokumen): bool
    {
        return $user->hasAnyRole(['Admin', 'Fakultas']);
    }

    public function delete(User $user, ManajemenDokumen $manajemenDokumen): bool
    {
        return $user->hasAnyRole(['Admin', 'Fakultas']);
    }
}
