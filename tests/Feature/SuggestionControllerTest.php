<?php

use App\Models\Page;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires authentication to submit a suggestion', function () {
    $page = Page::factory()->published()->create();

    $this->post(route('suggestions.store'), [
        'type' => Page::class,
        'id' => $page->id,
        'body' => 'This paragraph is missing a comma.',
    ])->assertRedirect(route('login'));
});

it('lets an authenticated user submit a suggestion tied to the right content', function () {
    $user = User::factory()->create();
    $page = Page::factory()->published()->create();

    $this->actingAs($user)
        ->post(route('suggestions.store'), [
            'type' => Page::class,
            'id' => $page->id,
            'body' => 'This paragraph is missing a comma.',
        ])
        ->assertOk();

    $this->assertDatabaseHas('suggestions', [
        'user_id' => $user->id,
        'suggestable_type' => Page::class,
        'suggestable_id' => $page->id,
        'body' => 'This paragraph is missing a comma.',
        'status' => 'pending',
    ]);
});

it('rejects a suggestion body that is too short', function () {
    $user = User::factory()->create();
    $page = Page::factory()->published()->create();

    $this->actingAs($user)
        ->post(route('suggestions.store'), [
            'type' => Page::class,
            'id' => $page->id,
            'body' => 'Hi',
        ])
        ->assertSessionHasErrors('body');

    expect(Suggestion::count())->toBe(0);
});

it('ties a suggestion to the stable content id even for a superseded version', function () {
    $user = User::factory()->create();
    $original = Page::factory()->published()->create(['title' => 'Movement']);

    $newest = Page::factory()->create([
        'title' => 'Movement',
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
        'published_by' => User::factory(),
    ]);
    $original->update(['newest' => $newest->id]);

    $this->actingAs($user)
        ->post(route('suggestions.store'), [
            'type' => Page::class,
            'id' => $newest->id,
            'body' => 'Please clarify this rule further.',
        ])
        ->assertOk();

    $this->assertDatabaseHas('suggestions', [
        'suggestable_type' => Page::class,
        'suggestable_id' => $original->id,
    ]);
});
