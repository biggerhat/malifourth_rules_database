<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the permissions table
     */
    public function run(): void
    {
        foreach (PermissionEnum::cases() as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }

        Permission::whereNotIn('name', PermissionEnum::cases())->delete();

        $role = Role::where('name', DatabaseSeeder::SUPER_ADMIN_ROLE)->first();

        if ($role) {
            $permissions = Permission::all();
            $role->syncPermissions($permissions);
        }
    }
}
