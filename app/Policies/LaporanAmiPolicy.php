<?php

namespace App\Policies;

use App\Models\LaporanAmi;
use App\Models\User;

class LaporanAmiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Auditor', 'Fakultas', 'Auditee', 'Unit Penunjang']);
    }

    public function view(User $user, LaporanAmi $laporanAmi): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Auditor']);
    }

    public function update(User $user, LaporanAmi $laporanAmi): bool
    {
        return $user->hasAnyRole(['Admin', 'Auditor']);
    }

    public function delete(User $user, LaporanAmi $laporanAmi): bool
    {
        return $user->hasAnyRole(['Admin', 'Auditor']);
    }
}
