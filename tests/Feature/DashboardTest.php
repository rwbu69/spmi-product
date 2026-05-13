<?php

use App\Models\User;

// ── Dashboard ─────────────────────────────────────────────────────────────

test('tamu diarahkan ke login saat mengakses dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('admin dapat mengakses dashboard', function () {
    $admin = userWithRole('Admin');
    $this->actingAs($admin)->get(route('dashboard'))->assertOk();
});

test('auditor dapat mengakses dashboard', function () {
    $auditor = userWithRole('Auditor');
    $this->actingAs($auditor)->get(route('dashboard'))->assertOk();
});

test('auditee dapat mengakses dashboard', function () {
    $auditee = userWithRole('Auditee');
    $this->actingAs($auditee)->get(route('dashboard'))->assertOk();
});

test('fakultas dapat mengakses dashboard', function () {
    $fakultas = userWithRole('Fakultas');
    $this->actingAs($fakultas)->get(route('dashboard'))->assertOk();
});

// ── Admin: Full Access ────────────────────────────────────────────────────

test('admin dapat mengakses semua menu referensi', function () {
    $admin = userWithRole('Admin');

    $routes = [
        '/referensi/tahun-periode',
        '/referensi/lembaga-akreditasi',
        '/referensi/auditee',
        '/referensi/unit-penunjang',
    ];

    foreach ($routes as $route) {
        $this->actingAs($admin)->get($route)->assertOk();
    }
});

test('admin dapat mengakses menu penetapan', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->get('/penetapan/standar-mutu')->assertOk();
    $this->actingAs($admin)->get('/penetapan/nilai-mutu')->assertOk();
});

test('admin dapat mengakses menu pelaksanaan', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->get('/pelaksanaan/pengaturan-periode')->assertOk();
    $this->actingAs($admin)->get('/pelaksanaan/evaluasi-diri')->assertOk();
});

test('admin dapat mengakses menu evaluasi AMI', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->get('/ami/auditor')->assertOk();
    $this->actingAs($admin)->get('/ami/laporan-ami')->assertOk();
    $this->actingAs($admin)->get('/ami/rekap-desk-eval')->assertOk();
});

test('admin dapat mengakses menu pengendalian', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->get('/pengendalian/daftar-temuan')->assertOk();
    $this->actingAs($admin)->get('/pengendalian/kesesuaian')->assertOk();
});

test('admin dapat mengakses pengaturan sistem', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->get('/pengaturan/pengguna-backoffice')->assertOk();
});

// ── Shared Data: Roles di-pass ke Inertia ────────────────────────────────

test('roles pengguna tersedia di shared inertia props', function () {
    $auditor = userWithRole('Auditor');

    $response = $this->actingAs($auditor)->get(route('dashboard'));

    $response->assertInertia(fn ($page) =>
        $page->has('auth.roles')
             ->where('auth.roles.0', 'Auditor')
    );
});

test('role tunggal tersedia di auth.role', function () {
    $admin = userWithRole('Admin');

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertInertia(fn ($page) =>
        $page->has('auth.role')
             ->where('auth.role', 'Admin')
    );
});