<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view users',
            'view own user',
            'create users',
            'update users',
            'update own user',
            'delete users',

            'view products',

            'view favorites',
            'view own favorites',
            'create favorites',
            'create own favorites',
            'delete favorites',
            'delete own favorites',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $clientRole = Role::create(['name' => 'client']);
        $clientRole->givePermissionTo([
            'view own user',
            'update own user',
            'view products',
            'view own favorites',
            'create own favorites',
            'delete own favorites',
        ]);
    }
}
