<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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

        $role = Role::firstOrCreate([
            'name' => self::SUPER_ADMIN_ROLE,
            'guard_name' => 'web',
        ]);

        $permissions = Permission::all();
        $role->syncPermissions($permissions);

        $admin = User::where('email', 'admin@test.com')->first()
            ?? User::factory()->create([
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
            ]);

        $admin->assignRole(self::SUPER_ADMIN_ROLE);

        $this->command->info('Admin user ready: admin@test.com / password');

        $this->call([
            RulesContentSeeder::class,
            FaqSeeder::class,
            GainingGroundsSeeder::class,
            ErrataSeeder::class,
        ]);
    }
}
