<?php

use App\Models\Season;
use App\Models\SeasonPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published, current season pages', function () {
    SeasonPage::factory()->published()->create(['title' => 'Published Page']);
    SeasonPage::factory()->create(['title' => 'Draft Page']);

    $response = $this->getJson('/api/v1/season-pages');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Published Page');
});

it('filters season pages by season slug', function () {
    $seasonOne = Season::factory()->published()->create();
    $seasonTwo = Season::factory()->published()->create();
    SeasonPage::factory()->published()->for($seasonOne)->create(['title' => 'From Season One']);
    SeasonPage::factory()->published()->for($seasonTwo)->create(['title' => 'From Season Two']);

    $response = $this->getJson("/api/v1/season-pages?season={$seasonOne->slug}");

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('From Season One');
});

it('returns a single published season page by slug', function () {
    $seasonPage = SeasonPage::factory()->published()->create();

    $response = $this->getJson("/api/v1/season-pages/{$seasonPage->slug}");

    $response->assertOk()->assertJsonPath('data.slug', $seasonPage->slug);
});

it('returns 404 for an unpublished season page', function () {
    $seasonPage = SeasonPage::factory()->create();

    $this->getJson("/api/v1/season-pages/{$seasonPage->slug}")->assertStatus(404);
});
