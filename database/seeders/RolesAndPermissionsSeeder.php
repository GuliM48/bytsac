<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $adminRole = Role::firstOrCreate(['name' => 'administrador']);
        $commercialRole = Role::firstOrCreate(['name' => 'comercial']);
        $clientRole = Role::firstOrCreate(['name' => 'cliente']);

        $permissions = [
            'view clients',
            'create clients',
            'edit clients',
            'delete clients',
            'view plans',
            'create plans',
            'edit plans',
            'delete plans',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole->syncPermissions($permissions);
        $commercialRole->syncPermissions([
            'view clients',
            'create clients',
            'edit clients',
            'view plans',
        ]);
        $clientRole->syncPermissions([
            'view plans',
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@bytsac.pe'],
            [
                'name' => 'Administrador BYTSAC',
                // Change this password in production.
                'password' => bcrypt('Admin@2026!'),
                'tenant_id' => 1,
            ]
        );  

        $admin->syncRoles([$adminRole]);
    }
}

