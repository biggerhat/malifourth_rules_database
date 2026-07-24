<?php

use App\Models\Errata;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('exposes a flat json shape matching what ErrataView.vue expects for the admin list preview button', function () {
    $this->seed(PermissionSeeder::class);
    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
    $role->syncPermissions(Permission::all());
    $editor = User::factory()->create();
    $editor->assignRole($role);

    $errata = Errata::factory()->create([
        'title' => 'Clarified Rule',
        'content' => 'Body text',
    ]);

    $response = $this->actingAs($editor)->get(route('admin.errata.view', $errata));

    $response->assertOk();
    $response->assertJsonPath('title', 'Clarified Rule');
    $response->assertJsonPath('slug', $errata->slug);
    $response->assertJsonPath('content.0.text', 'Body text');
});
