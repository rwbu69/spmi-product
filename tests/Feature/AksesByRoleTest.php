<?php

// ── Auditee: Menu yang Dapat Diakses ─────────────────────────────────────

test('auditee dapat mengakses evaluasi diri', function () {
    $auditee = userWithRole('Auditee');

    $this->actingAs($auditee)
        ->get('/pelaksanaan/evaluasi-diri')
        ->assertOk();
});

test('auditee dapat mengakses daftar kesesuaian', function () {
    $auditee = userWithRole('Auditee');

    $this->actingAs($auditee)
        ->get('/pengendalian/kesesuaian')
        ->assertOk();
});

test('auditee dapat mengakses daftar temuan', function () {
    $auditee = userWithRole('Auditee');

    $this->actingAs($auditee)
        ->get('/pengendalian/daftar-temuan')
        ->assertOk();
});

test('auditee dapat mengakses manajemen dokumen', function () {
    $auditee = userWithRole('Auditee');

    $this->actingAs($auditee)
        ->get('/dokumen/manajemen')
        ->assertOk();
});

// ── Auditee: Menu yang TIDAK Dapat Diakses ────────────────────────────────

test('auditee tidak dapat mengakses manajemen referensi', function () {
    $auditee = userWithRole('Auditee');

    $routes = [
        '/referensi/tahun-periode',
        '/referensi/lembaga-akreditasi',
        '/referensi/auditee',
    ];

    foreach ($routes as $route) {
        $this->actingAs($auditee)->get($route)->assertForbidden();
    }
});

test('auditee tidak dapat mengakses penetapan standar mutu', function () {
    $auditee = userWithRole('Auditee');

    $this->actingAs($auditee)
        ->get('/penetapan/standar-mutu')
        ->assertForbidden();
});

test('auditee tidak dapat mengakses pengaturan sistem', function () {
    $auditee = userWithRole('Auditee');

    $this->actingAs($auditee)
        ->get('/pengaturan/pengguna-backoffice')
        ->assertForbidden();
});

// ── Fakultas: Menu yang Dapat Diakses ────────────────────────────────────

test('fakultas dapat mengakses rekap desk evaluation', function () {
    $fakultas = userWithRole('Fakultas');

    // Route /ami/rekap-desk-eval kini dapat diakses Admin|Fakultas
    $this->actingAs($fakultas)
        ->get('/ami/rekap-desk-eval')
        ->assertOk();
});

test('fakultas dapat mengakses daftar temuan', function () {
    $fakultas = userWithRole('Fakultas');

    $this->actingAs($fakultas)
        ->get('/pengendalian/daftar-temuan')
        ->assertOk();
});

test('fakultas tidak dapat mengakses pengaturan sistem', function () {
    $fakultas = userWithRole('Fakultas');

    $this->actingAs($fakultas)
        ->get('/pengaturan/pengguna-backoffice')
        ->assertForbidden();
});

test('fakultas tidak dapat mengakses menu khusus auditor', function () {
    $fakultas = userWithRole('Fakultas');

    $this->actingAs($fakultas)
        ->get('/auditor/desk-evaluation')
        ->assertForbidden();
});
