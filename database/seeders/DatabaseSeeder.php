<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public const SUPER_ADMIN_ROLE = 'Super Admin';

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
        ]);

        $role = Role::create([
            'name' => self::SUPER_ADMIN_ROLE,
            'guard_name' => 'web',
        ]);

        $permissions = Permission::all();
        $role->syncPermissions($permissions);
    }
}
