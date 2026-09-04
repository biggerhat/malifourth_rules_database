<?php

use App\Enums\IndexTypeEnum;
use App\Models\Index;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published, current indices', function () {
    Index::factory()->published()->create(['title' => 'Published Index']);
    Index::factory()->create(['title' => 'Draft Index']);

    $response = $this->getJson('/api/v1/indices');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Published Index');
});

it('filters indices by type', function () {
    Index::factory()->published()->create(['title' => 'Text Entry', 'type' => IndexTypeEnum::Text]);
    Index::factory()->published()->create(['title' => 'Image Entry', 'type' => IndexTypeEnum::Image]);

    $response = $this->getJson('/api/v1/indices?type=image');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Image Entry');
});

it('returns a single published index entry by slug', function () {
    $index = Index::factory()->published()->create();

    $response = $this->getJson("/api/v1/indices/{$index->slug}");

    $response->assertOk()->assertJsonPath('data.slug', $index->slug);
});

it('returns 404 for an unpublished index entry', function () {
    $index = Index::factory()->create();

    $this->getJson("/api/v1/indices/{$index->slug}")->assertStatus(404);
});
