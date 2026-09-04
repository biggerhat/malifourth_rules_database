<?php

use App\Models\Scheme;
use App\Models\Season;
use App\Models\SeasonPage;
use App\Models\Strategy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published, current seasons', function () {
    Season::factory()->published()->create(['title' => 'Published Season']);
    Season::factory()->create(['title' => 'Draft Season']);

    $response = $this->getJson('/api/v1/seasons');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Published Season');
});

it('returns a single season with its published strategies, schemes, and pages', function () {
    $season = Season::factory()->published()->create();
    Strategy::factory()->published()->for($season)->create(['title' => 'Published Strategy']);
    Strategy::factory()->for($season)->create(['title' => 'Draft Strategy']);
    Scheme::factory()->published()->for($season)->create(['title' => 'Published Scheme']);
    SeasonPage::factory()->published()->for($season)->create(['title' => 'Published Page']);

    $response = $this->getJson("/api/v1/seasons/{$season->slug}");

    $response->assertOk();
    expect($response->json('data.strategies'))->toHaveCount(1);
    expect($response->json('data.schemes'))->toHaveCount(1);
    expect($response->json('data.season_pages'))->toHaveCount(1);
});

it('returns 404 for an unpublished season', function () {
    $season = Season::factory()->create();

    $this->getJson("/api/v1/seasons/{$season->slug}")->assertStatus(404);
});
