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
            [
                'name' => 'Merlin',
                'username' => 'merlin',
                'password' => Hash::make('password')
            ]
        );
        $auditee->assignRole('Auditee');

        $admin = User::firstOrCreate(
            [
                'name' => 'Admin2',
                'username' => 'admin2',
                'password' => Hash::make('password')
            ]
        );
        $admin->assignRole('Admin');

        $auditor = User::firstOrCreate(
            [
                'name' => 'Arman',
                'username' => 'arman',
                'password' => Hash::make('password')
            ]
        );
        $auditor->assignRole('Auditor');
    }
}
