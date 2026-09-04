<?php

use App\Models\Page;
use App\Models\Suggestion;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function actingSuggestionReviewer(): User
{
    test()->seed(PermissionSeeder::class);
    $role = Role::create(['name' => 'Suggestion Reviewer', 'guard_name' => 'web']);
    $role->syncPermissions(Permission::all());
    $reviewer = User::factory()->create();
    $reviewer->assignRole($role);

    return $reviewer;
}

it('blocks an unauthorized user from the suggestions admin list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.suggestions.index'))
        ->assertForbidden();
});

it('lets a permitted user see pending suggestions with the linked content', function () {
    $reviewer = actingSuggestionReviewer();
    $submitter = User::factory()->create();
    $page = Page::factory()->published()->create(['title' => 'Movement']);

    Suggestion::create([
        'user_id' => $submitter->id,
        'suggestable_type' => Page::class,
        'suggestable_id' => $page->id,
        'body' => 'This needs a diagram.',
    ]);

    $this->actingAs($reviewer)
        ->get(route('admin.suggestions.index'))
        ->assertInertia(fn ($p) => $p
            ->has('suggestions', 1)
            ->where('suggestions.0.status', 'pending')
            ->where('suggestions.0.content.type', 'Page')
        );
});

it('lets a permitted user mark a suggestion reviewed', function () {
    $reviewer = actingSuggestionReviewer();
    $page = Page::factory()->published()->create();

    $suggestion = Suggestion::create([
        'user_id' => User::factory()->create()->id,
        'suggestable_type' => Page::class,
        'suggestable_id' => $page->id,
        'body' => 'A suggestion.',
    ]);

    $this->actingAs($reviewer)
        ->post(route('admin.suggestions.review', $suggestion))
        ->assertRedirect();

    expect($suggestion->fresh())
        ->status->toBe('reviewed')
        ->reviewed_by->toBe($reviewer->id);
});

it('lets a permitted user dismiss a suggestion', function () {
    $reviewer = actingSuggestionReviewer();
    $page = Page::factory()->published()->create();

    $suggestion = Suggestion::create([
        'user_id' => User::factory()->create()->id,
        'suggestable_type' => Page::class,
        'suggestable_id' => $page->id,
        'body' => 'A suggestion.',
    ]);

    $this->actingAs($reviewer)
        ->post(route('admin.suggestions.dismiss', $suggestion))
        ->assertRedirect();

    expect($suggestion->fresh())->status->toBe('dismissed');
});

it('blocks an unauthorized user from reviewing a suggestion', function () {
    $user = User::factory()->create();
    $page = Page::factory()->published()->create();

    $suggestion = Suggestion::create([
        'user_id' => User::factory()->create()->id,
        'suggestable_type' => Page::class,
        'suggestable_id' => $page->id,
        'body' => 'A suggestion.',
    ]);

    $this->actingAs($user)
        ->post(route('admin.suggestions.review', $suggestion))
        ->assertForbidden();
});
