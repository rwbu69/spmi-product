<?php

use App\Models\User;

// ── User Model ────────────────────────────────────────────────────────────

test('user dapat dibuat dengan field username', function () {
    $user = User::factory()->create([
        'name'     => 'Budi Santoso',
        'username' => 'budi_santoso',
        'email'    => 'budi@example.com',
    ]);

    expect($user->username)->toBe('budi_santoso');
    expect($user->name)->toBe('Budi Santoso');
});

test('username harus unik di database', function () {
    User::factory()->create(['username' => 'unik_user']);

    expect(fn () => User::factory()->create(['username' => 'unik_user']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('user dapat di-soft-delete', function () {
    $user = User::factory()->create();

    $user->delete();

    // Tidak muncul di query normal
    $this->assertDatabaseMissing('users', [
        'id'         => $user->id,
        'deleted_at' => null,
    ]);

    // Masih ada dengan deleted_at terisi
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

// ── Manajemen Pengguna via Controller (Admin) ─────────────────────────────

test('admin dapat mengakses halaman manajemen pengguna', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)
        ->get('/pengaturan/pengguna-backoffice')
        ->assertOk();
});

test('non-admin tidak dapat mengakses manajemen pengguna', function () {
    foreach (['Auditor', 'Fakultas', 'Auditee'] as $role) {
        $user = userWithRole($role);

        $this->actingAs($user)
            ->get('/pengaturan/pengguna-backoffice')
            ->assertForbidden();
    }
});

test('admin dapat membuat pengguna baru dengan role', function () {
    seedRoles();
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post('/pengaturan/pengguna-backoffice', [
        'name'     => 'Test User Baru',
        'username' => 'test_user_baru',
        'email'    => 'testbaru@example.com',
        'password' => 'password123',
        'role'     => 'Auditor',
    ])->assertRedirect();

    $this->assertDatabaseHas('users', [
        'username' => 'test_user_baru',
        'email'    => 'testbaru@example.com',
    ]);

    $created = User::where('email', 'testbaru@example.com')->first();
    expect($created->hasRole('Auditor'))->toBeTrue();
});

test('membuat pengguna tanpa username menghasilkan error validasi', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post('/pengaturan/pengguna-backoffice', [
        'name'     => 'Test',
        'email'    => 'testnouser@example.com',
        'password' => 'password123',
        'role'     => 'Auditor',
    ])->assertSessionHasErrors('username');
});

test('membuat pengguna tanpa role menghasilkan error validasi', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)->post('/pengaturan/pengguna-backoffice', [
        'name'     => 'Test',
        'username' => 'test_norol',
        'email'    => 'testnorol@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors('role');
});

test('admin dapat mengupdate nama pengguna', function () {
    seedRoles();
    $admin  = userWithRole('Admin');
    $target = userWithRole('Auditor');

    $this->actingAs($admin)->put("/pengaturan/pengguna-backoffice/{$target->id}", [
        'name'     => 'Nama Telah Diubah',
        'username' => $target->username,
        'email'    => $target->email,
        'role'     => 'Auditor',
    ])->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id'   => $target->id,
        'name' => 'Nama Telah Diubah',
    ]);
});

test('admin dapat mengubah role pengguna', function () {
    seedRoles();
    $admin  = userWithRole('Admin');
    $target = userWithRole('Auditor');

    $this->actingAs($admin)->put("/pengaturan/pengguna-backoffice/{$target->id}", [
        'name'     => $target->name,
        'username' => $target->username,
        'email'    => $target->email,
        'role'     => 'Fakultas',
    ])->assertRedirect();

    $target->refresh();
    expect($target->hasRole('Fakultas'))->toBeTrue();
    expect($target->hasRole('Auditor'))->toBeFalse();
});

test('admin dapat menghapus pengguna lain', function () {
    $admin    = userWithRole('Admin');
    $target   = userWithRole('Auditee');
    $targetId = $target->id;

    $this->actingAs($admin)
        ->delete("/pengaturan/pengguna-backoffice/{$targetId}")
        ->assertRedirect()
        ->assertSessionHas('success');

    // Soft-deleted: deleted_at terisi
    $this->assertDatabaseMissing('users', [
        'id'         => $targetId,
        'deleted_at' => null,
    ]);
});

test('admin tidak dapat menghapus diri sendiri', function () {
    $admin = userWithRole('Admin');

    $this->actingAs($admin)
        ->delete("/pengaturan/pengguna-backoffice/{$admin->id}")
        ->assertRedirect()
        ->assertSessionHas('error');

    // Admin masih ada dan tidak terhapus
    $this->assertDatabaseHas('users', [
        'id'         => $admin->id,
        'deleted_at' => null,
    ]);
});
