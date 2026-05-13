<?php

use App\Models\User;

// ── Login ─────────────────────────────────────────────────────────────────

test('halaman login dapat diakses', function () {
    $this->get(route('login'))->assertOk();
});

test('pengguna dapat login dengan username dan password yang benar', function () {
    User::factory()->create([
        'username' => 'user_login_test',
        'password' => bcrypt('password'),
    ]);

    // Fortify dikonfigurasi dengan 'username' => 'username' di config/fortify.php
    $this->post(route('login.store'), [
        'username' => 'user_login_test',
        'password' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});

test('pengguna tidak dapat login dengan password salah', function () {
    $user = User::factory()->create(['username' => 'user_wrong_pass']);

    $this->post(route('login.store'), [
        'username' => 'user_wrong_pass',
        'password'  => 'salah-password',
    ]);

    $this->assertGuest();
});

test('pengguna tidak dapat login dengan username yang tidak ada', function () {
    $this->post(route('login.store'), [
        'username' => 'tidak_ada_123',
        'password'  => 'password',
    ]);

    $this->assertGuest();
});

test('pengguna dapat logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('logout'));
    $this->assertGuest();
});

// ── Guest Redirect ────────────────────────────────────────────────────────

test('tamu diarahkan ke login saat mengakses rute yang dilindungi', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});