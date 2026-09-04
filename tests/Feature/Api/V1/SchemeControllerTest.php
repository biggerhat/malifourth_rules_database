<?php

use App\Models\Scheme;
use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published, current schemes', function () {
    Scheme::factory()->published()->create(['title' => 'Published Scheme']);
    Scheme::factory()->create(['title' => 'Draft Scheme']);

    $response = $this->getJson('/api/v1/schemes');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Published Scheme');
});

it('filters schemes by season slug', function () {
    $season = Season::factory()->published()->create();
    Scheme::factory()->published()->for($season)->create(['title' => 'In Season']);
    Scheme::factory()->published()->create(['title' => 'Other Season']);

    $response = $this->getJson("/api/v1/schemes?season={$season->slug}");

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('In Season');
});

it('returns a single published scheme by slug', function () {
    $scheme = Scheme::factory()->published()->create();

    $response = $this->getJson("/api/v1/schemes/{$scheme->slug}");

    $response->assertOk()->assertJsonPath('data.slug', $scheme->slug);
});

it('returns 404 for an unpublished scheme', function () {
    $scheme = Scheme::factory()->create();

    $this->getJson("/api/v1/schemes/{$scheme->slug}")->assertStatus(404);
});
