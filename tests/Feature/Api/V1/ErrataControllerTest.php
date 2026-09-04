<?php

use App\Models\Errata;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published, current errata', function () {
    Errata::factory()->published()->create(['title' => 'Published Errata']);
    Errata::factory()->create(['title' => 'Draft Errata']);

    $response = $this->getJson('/api/v1/errata');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Published Errata');
});

it('excludes superseded errata versions from the list', function () {
    $publisher = User::factory()->create();
    $original = Errata::factory()->published()->create(['title' => 'Old']);
    $newest = Errata::factory()->create([
        'title' => 'New',
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
        'published_by' => $publisher->id,
    ]);
    $original->update(['newest' => $newest->id]);

    $response = $this->getJson('/api/v1/errata');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('New');
});

it('returns a single published errata entry by slug', function () {
    $errata = Errata::factory()->published()->create(['content' => 'Fixed a typo.']);

    $response = $this->getJson("/api/v1/errata/{$errata->slug}");

    $response->assertOk()->assertJsonPath('data.slug', $errata->slug);
});

it('returns 404 for an unpublished errata entry', function () {
    $errata = Errata::factory()->create();

    $this->getJson("/api/v1/errata/{$errata->slug}")->assertStatus(404);
});
