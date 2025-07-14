<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Auth & Dashboard
            'admin.dashboard.index',
            
            // Permissions Management
            'admin.permissions.index',
            'admin.permissions.show',
            
            // Roles Management
            'admin.roles.index',
            'admin.roles.store',
            'admin.roles.show',
            'admin.roles.update',
            'admin.roles.destroy',
            
            // Users Management
            'admin.users.index',
            'admin.users.store',
            'admin.users.show',
            'admin.users.update',
            'admin.users.destroy',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Create default roles
        $adminRole = Role::updateOrCreate(
            ['name' => 'Admin'],
            ['is_default' => false]
        );

        $companyRole = Role::updateOrCreate(
            ['name' => 'Company'],
            ['is_default' => false]
        );

        // Assign all permissions to Super Admin
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));
    }
}
