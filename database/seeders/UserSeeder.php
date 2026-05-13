<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $auditee = User::firstOrCreate(
            ['email' => 'merlin@example.com'],
            [
                'name' => 'Merlin',
                'username' => 'merlin',
                'password' => Hash::make('password')
            ]
        );
        $auditee->assignRole('Auditee');

        $auditor = User::firstOrCreate(
            ['email' => 'arman@example.com'],
            [
                'name' => 'Arman',
                'username' => 'arman',
                'password' => Hash::make('password')
            ]
        );
        $auditor->assignRole('Auditor');
    }
}
