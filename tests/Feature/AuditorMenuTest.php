<?php

// ── Menu Auditor: Audit ───────────────────────────────────────────────────

test('auditor dapat mengakses halaman desk evaluation', function () {
    $auditor = userWithRole('Auditor');

    $this->actingAs($auditor)
        ->get('/auditor/desk-evaluation')
        ->assertOk();
});

test('auditor dapat mengakses halaman visitasi', function () {
    $auditor = userWithRole('Auditor');

    $this->actingAs($auditor)
        ->get('/auditor/visitasi')
        ->assertOk();
});

test('auditor dapat mengakses halaman download laporan AMI', function () {
    $auditor = userWithRole('Auditor');

    $this->actingAs($auditor)
        ->get('/auditor/download-laporan-ami')
        ->assertOk();
});

test('auditor dapat mengakses halaman upload laporan AMI', function () {
    $auditor = userWithRole('Auditor');

    $this->actingAs($auditor)
        ->get('/auditor/upload-laporan-ami')
        ->assertOk();
});

// ── Menu Auditor: Laporan ─────────────────────────────────────────────────

test('auditor dapat mengakses rekap daftar temuan', function () {
    $auditor = userWithRole('Auditor');

    $this->actingAs($auditor)
        ->get('/auditor/rekap-temuan')
        ->assertOk();
});

test('auditor dapat mengakses rekap daftar kesesuaian', function () {
    $auditor = userWithRole('Auditor');

    $this->actingAs($auditor)
        ->get('/auditor/rekap-kesesuaian')
        ->assertOk();
});

// ── Role Lain Tidak Dapat Mengakses Menu Auditor ──────────────────────────

test('admin tidak dapat mengakses menu khusus auditor', function () {
    $admin = userWithRole('Admin');

    $routes = [
        '/auditor/desk-evaluation',
        '/auditor/visitasi',
        '/auditor/download-laporan-ami',
        '/auditor/upload-laporan-ami',
        '/auditor/rekap-temuan',
        '/auditor/rekap-kesesuaian',
    ];

    foreach ($routes as $route) {
        $this->actingAs($admin)->get($route)->assertForbidden();
    }
});

test('auditee tidak dapat mengakses menu khusus auditor', function () {
    $auditee = userWithRole('Auditee');

    $this->actingAs($auditee)
        ->get('/auditor/desk-evaluation')
        ->assertForbidden();
});

test('tamu tidak dapat mengakses menu auditor', function () {
    $this->get('/auditor/desk-evaluation')->assertRedirect(route('login'));
});

// ── Upload Laporan AMI ────────────────────────────────────────────────────

test('validasi upload laporan AMI: file dan periode wajib diisi', function () {
    $auditor = userWithRole('Auditor');

    $this->actingAs($auditor)
        ->post('/auditor/upload-laporan-ami', [
            // Semua field kosong
        ])
        ->assertSessionHasErrors(['pengaturan_periode_id', 'auditee_id', 'file_laporan', 'tanggal_laporan', 'status']);
});
