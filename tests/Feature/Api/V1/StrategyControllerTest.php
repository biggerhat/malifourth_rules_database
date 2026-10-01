<?php

use App\Enums\SuitEnum;
use App\Models\Season;
use App\Models\Strategy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published, current strategies', function () {
    Strategy::factory()->published()->create(['title' => 'Published Strategy']);
    Strategy::factory()->create(['title' => 'Draft Strategy']);

    $response = $this->getJson('/api/v1/strategies');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Published Strategy');
});

it('filters strategies by suit', function () {
    Strategy::factory()->published()->create(['title' => 'Ram Strategy', 'suit' => SuitEnum::Rams]);
    Strategy::factory()->published()->create(['title' => 'Mask Strategy', 'suit' => SuitEnum::Masks]);

    $response = $this->getJson('/api/v1/strategies?suit=masks');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Mask Strategy');
});

it('filters strategies by season slug', function () {
    $season = Season::factory()->published()->create();
    Strategy::factory()->published()->for($season)->create(['title' => 'In Season']);
    Strategy::factory()->published()->create(['title' => 'Other Season']);

    $response = $this->getJson("/api/v1/strategies?season={$season->slug}");

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('In Season');
});

it('returns 404 for an unpublished strategy', function () {
    $strategy = Strategy::factory()->create();

    $this->getJson("/api/v1/strategies/{$strategy->slug}")->assertStatus(404);
});
