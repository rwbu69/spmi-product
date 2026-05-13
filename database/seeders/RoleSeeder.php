<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'Administrator']);
        $leadAuditor = Role::firstOrCreate(['name' => 'Lead Auditor']);
        $auditor = Role::firstOrCreate(['name' => 'Auditor']);
        $auditee = Role::firstOrCreate(['name' => 'Auditee']);
        $pimpinan = Role::firstOrCreate(['name' => 'Pimpinan']);

        // Optional: Assign some permissions (you can define permissions later based on features)
        // e.g. Permission::create(['name' => 'manage users']);
        // $admin->givePermissionTo('manage users');
    }
}
