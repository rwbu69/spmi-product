<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

// ── Seeding ───────────────────────────────────────────────────────────────

test('empat role dapat dibuat', function () {
    seedRoles();

    expect(Role::count())->toBe(4);
    expect(Role::pluck('name')->sort()->values()->toArray())
        ->toBe(['Admin', 'Auditee', 'Auditor', 'Fakultas']);
});

// ── Penugasan Role ────────────────────────────────────────────────────────

test('pengguna dapat ditugaskan sebagai Admin', function () {
    $user = userWithRole('Admin');
    expect($user->hasRole('Admin'))->toBeTrue();
});

test('pengguna dapat ditugaskan sebagai Auditor', function () {
    $user = userWithRole('Auditor');
    expect($user->hasRole('Auditor'))->toBeTrue();
});

test('pengguna dapat ditugaskan sebagai Fakultas', function () {
    $user = userWithRole('Fakultas');
    expect($user->hasRole('Fakultas'))->toBeTrue();
});

test('pengguna dapat ditugaskan sebagai Auditee', function () {
    $user = userWithRole('Auditee');
    expect($user->hasRole('Auditee'))->toBeTrue();
});

test('pengguna hanya memiliki satu role aktif', function () {
    seedRoles();
    $user = User::factory()->create();
    $user->assignRole('Admin');

    expect($user->getRoleNames()->count())->toBe(1);

    // Sync ke role lain
    $user->syncRoles(['Auditor']);
    expect($user->getRoleNames()->count())->toBe(1);
    expect($user->hasRole('Auditor'))->toBeTrue();
    expect($user->hasRole('Admin'))->toBeFalse();
});

// ── Keunikan Role Tidak Duplikat ──────────────────────────────────────────

test('role tidak terduplikat saat di-seed berulang', function () {
    seedRoles();
    seedRoles(); // panggil dua kali

    expect(Role::count())->toBe(4);
});
