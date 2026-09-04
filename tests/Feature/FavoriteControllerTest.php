<?php

use App\Models\Favorite;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires authentication to toggle a favorite', function () {
    $page = Page::factory()->published()->create();

    $this->post(route('favorites.toggle'), [
        'type' => Page::class,
        'id' => $page->id,
    ])->assertRedirect(route('login'));
});

it('lets an authenticated user favorite and unfavorite a page', function () {
    $user = User::factory()->create();
    $page = Page::factory()->published()->create();

    $this->actingAs($user)
        ->post(route('favorites.toggle'), ['type' => Page::class, 'id' => $page->id])
        ->assertOk()
        ->assertJson(['favorited' => true]);

    $this->assertDatabaseHas('favorites', [
        'user_id' => $user->id,
        'favoritable_type' => Page::class,
        'favoritable_id' => $page->id,
    ]);

    $this->actingAs($user)
        ->post(route('favorites.toggle'), ['type' => Page::class, 'id' => $page->id])
        ->assertOk()
        ->assertJson(['favorited' => false]);

    $this->assertDatabaseMissing('favorites', [
        'user_id' => $user->id,
        'favoritable_type' => Page::class,
        'favoritable_id' => $page->id,
    ]);
});

it('scopes favorites to the user who created them', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $page = Page::factory()->published()->create(['title' => 'Movement']);

    Favorite::create(['user_id' => $owner->id, 'favoritable_type' => Page::class, 'favoritable_id' => $page->id]);

    $this->actingAs($owner)->get(route('favorites.index'))
        ->assertInertia(fn ($p) => $p->has('favorites', 1));

    $this->actingAs($other)->get(route('favorites.index'))
        ->assertInertia(fn ($p) => $p->has('favorites', 0));
});

it('keeps a favorite pointed at the current version after the favorited page is edited', function () {
    $user = User::factory()->create();
    $original = Page::factory()->published()->create(['title' => 'Movement', 'content' => 'Old text']);

    $original->toggleFavorite($user);

    $newest = Page::factory()->create([
        'title' => 'Movement',
        'content' => 'New text',
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
        'published_by' => User::factory(),
    ]);
    $original->update(['newest' => $newest->id]);
    $original->delete();

    expect($newest->isFavoritedBy($user))->toBeTrue();

    $this->actingAs($user)->get(route('favorites.index'))
        ->assertInertia(fn ($p) => $p
            ->has('favorites', 1)
            ->where('favorites.0.url', route('rules.page.view', $newest->slug))
        );
});

it('silently omits a favorite whose content is no longer published', function () {
    $user = User::factory()->create();
    $page = Page::factory()->published()->create();

    $page->toggleFavorite($user);
    $page->update(['published_at' => null]);

    $this->actingAs($user)->get(route('favorites.index'))
        ->assertInertia(fn ($p) => $p->has('favorites', 0));
});
