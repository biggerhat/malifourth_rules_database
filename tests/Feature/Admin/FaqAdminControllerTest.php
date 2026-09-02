<?php

use App\Enums\FaqCategoryEnum;
use App\Models\Faq;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function actingEditor(): User
{
    test()->seed(PermissionSeeder::class);
    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
    $role->syncPermissions(Permission::all());
    $editor = User::factory()->create();
    $editor->assignRole($role);

    return $editor;
}

it('deletes a published faq so it no longer appears on the public faq page', function () {
    $editor = actingEditor();

    $faq = Faq::factory()->published()->create(['title' => 'Retired Question']);

    $this->actingAs($editor)
        ->post(route('admin.faqs.delete', $faq->slug))
        ->assertRedirect(route('admin.faqs.index'));

    $this->assertSoftDeleted('faqs', ['id' => $faq->id]);

    $response = $this->get(route('rules.faq.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('categories', fn ($categories) => collect($categories)
            ->pluck('items')
            ->flatten(1)
            ->pluck('title_text')
            ->doesntContain('Retired Question'))
    );
});

it('bulk deletes published faqs', function () {
    $editor = actingEditor();

    $faqs = Faq::factory()->published()->count(2)->create();

    $this->actingAs($editor)
        ->post(route('admin.faqs.bulk-delete'), ['ids' => $faqs->pluck('id')->toArray()])
        ->assertRedirect();

    foreach ($faqs as $faq) {
        $this->assertSoftDeleted('faqs', ['id' => $faq->id]);
    }
});

it('supports the Campaign faq category', function () {
    $editor = actingEditor();

    $faq = Faq::factory()->published()->create(['category' => FaqCategoryEnum::Campaign]);

    expect($faq->fresh()->category)->toBe(FaqCategoryEnum::Campaign);

    $response = $this->get(route('rules.faq.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('categories', fn ($categories) => collect($categories)
            ->pluck('key')
            ->contains(FaqCategoryEnum::Campaign->value))
    );
});
