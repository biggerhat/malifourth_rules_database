<?php

use App\Models\Scheme;
use App\Models\Season;
use App\Models\Strategy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the printable strategy and scheme pool for a published season', function () {
    $season = Season::factory()->published()->create(['title' => 'Season One']);
    Strategy::factory()->published()->create(['season_id' => $season->id, 'title' => 'Reconnoiter']);
    Scheme::factory()->published()->create(['season_id' => $season->id, 'title' => 'Vendetta']);

    $response = $this->get(route('rules.gaining-grounds.season.print', $season->slug));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Rules/GainingGrounds/PoolPrint')
        ->where('season.title', 'Season One')
        ->where('strategies.0.title', 'Reconnoiter')
        ->where('schemes.0.title', 'Vendetta')
    );
});

it('returns 404 for the print pool of an unpublished season', function () {
    $season = Season::factory()->create(['title' => 'Draft Season']);

    $this->get(route('rules.gaining-grounds.season.print', $season->slug))->assertStatus(404);
});

it('excludes unpublished strategies and schemes from the printable pool', function () {
    $season = Season::factory()->published()->create(['title' => 'Season Two']);
    Strategy::factory()->published()->create(['season_id' => $season->id, 'title' => 'Public Strategy']);
    Strategy::factory()->create(['season_id' => $season->id, 'title' => 'Draft Strategy']);

    $response = $this->get(route('rules.gaining-grounds.season.print', $season->slug));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('strategies', 1)->where('strategies.0.title', 'Public Strategy'));
});
