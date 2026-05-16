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
        if ($user->hasAnyRole(['Admin', 'Auditor', 'Fakultas'])) {
            return true;
        }

        if ($user->auditee_id) {
            return $laporanAmi->auditee_id === $user->auditee_id;
        }

        return false;
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
