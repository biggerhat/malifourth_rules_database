<?php

use App\Models\CardErrata;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('groups published cards by faction on the index', function () {
    CardErrata::factory()->published()->create(['faction' => 'guild', 'card_name' => 'Lucius Mattheson']);
    CardErrata::factory()->published()->create(['faction' => 'neverborn', 'card_name' => 'Zoraida']);
    CardErrata::factory()->create(['faction' => 'guild', 'card_name' => 'Unpublished Card']);

    $response = $this->get(route('errata.cards.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('factions.0.value', 'guild')
        ->where('factions.0.cards.0.card_name', 'Lucius Mattheson')
        ->where('factions.1.value', 'neverborn')
        ->where('factions.1.cards.0.card_name', 'Zoraida')
    );
});

it('excludes unpublished cards from the index entirely', function () {
    CardErrata::factory()->create(['faction' => 'guild', 'card_name' => 'Draft Card']);

    $response = $this->get(route('errata.cards.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('factions', []));
});

it('renders a single published card with its entries', function () {
    $card = CardErrata::factory()->published()->create(['card_name' => 'Lucius Mattheson']);
    $card->entries()->create(['what_changed' => 'Reduced defense', 'sort_order' => 0]);

    $response = $this->get(route('errata.cards.view', $card));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('card_name', 'Lucius Mattheson')
        ->where('entries.0.what_changed.0.text', 'Reduced defense')
    );
});

it('exposes front and back image fields independently, including when only one is set', function () {
    $card = CardErrata::factory()->published()->create([
        'card_name' => 'Lucius Mattheson',
        'front_image' => '/storage/card-errata/lucius/front.png',
        'back_image' => null,
    ]);

    $response = $this->get(route('errata.cards.view', $card));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('front_image', '/storage/card-errata/lucius/front.png')
        ->where('back_image', null)
    );
});

it('returns 404 for an unpublished card', function () {
    $card = CardErrata::factory()->create();

    $this->get(route('errata.cards.view', $card))->assertStatus(404);
});

it('redirects the history route to the live card once it is the newest version', function () {
    $card = CardErrata::factory()->published()->create();

    $this->get(route('errata.cards.history', $card))->assertRedirect(route('errata.cards.view', $card->slug));
});

it('shows a superseded card version on the history route without exposing it live', function () {
    $original = CardErrata::factory()->published()->create();
    $publisher = User::factory()->create();

    $newest = CardErrata::factory()->create([
        'faction' => $original->faction,
        'card_name' => $original->card_name,
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
        'published_by' => $publisher->id,
    ]);
    $original->update(['newest' => $newest->id]);
    $original->delete();

    $historyResponse = $this->get(route('errata.cards.history', $original->slug));
    $historyResponse->assertOk();
    $historyResponse->assertInertia(fn ($page) => $page->where('viewing_old_version', true));

    $this->get(route('errata.cards.view', $newest))->assertOk();
});
