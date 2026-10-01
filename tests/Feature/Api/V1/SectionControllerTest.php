<?php

use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published, current sections', function () {
    Section::factory()->published()->create(['title' => 'Published Section']);
    Section::factory()->create(['title' => 'Draft Section']);

    $response = $this->getJson('/api/v1/sections');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Published Section');
});

it('filters sections by a case-insensitive title search', function () {
    Section::factory()->published()->create(['title' => 'Terrain Rules']);
    Section::factory()->published()->create(['title' => 'Line of Sight']);

    $response = $this->getJson('/api/v1/sections?search=terrain');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Terrain Rules');
});

it('returns a single published section by slug', function () {
    $section = Section::factory()->published()->create(['left_column' => '<p>Cover.</p>']);

    $response = $this->getJson("/api/v1/sections/{$section->slug}");

    $response->assertOk()
        ->assertJsonPath('data.slug', $section->slug)
        ->assertJsonPath('data.left_column', '<p>Cover.</p>');
});

it('returns 404 for an unpublished section', function () {
    $section = Section::factory()->create();

    $this->getJson("/api/v1/sections/{$section->slug}")->assertStatus(404);
});
