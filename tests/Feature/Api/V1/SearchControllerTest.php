<?php

use App\Models\Faq;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('searches every content type by default', function () {
    Page::factory()->published()->create(['title' => 'Movement', 'content' => 'walking rules']);
    Faq::factory()->published()->create(['title' => 'Walking FAQ', 'answer' => 'walking question and answer']);

    $response = $this->getJson('/api/v1/search?q=walking');

    $response->assertOk();
    expect($response->json('results.pages'))->toHaveCount(1);
    expect($response->json('results.faqs'))->toHaveCount(1);
});

it('restricts results to the requested types', function () {
    Page::factory()->published()->create(['title' => 'Movement', 'content' => 'walking rules']);
    Faq::factory()->published()->create(['title' => 'Walking FAQ', 'answer' => 'walking question and answer']);

    $response = $this->getJson('/api/v1/search?q=walking&types=faqs');

    $response->assertOk();
    expect($response->json('results.pages'))->toBe([]);
    expect($response->json('results.faqs'))->toHaveCount(1);
});

it('an unrecognized type value is ignored and falls back to searching everything', function () {
    Page::factory()->published()->create(['title' => 'Movement', 'content' => 'walking rules']);

    $response = $this->getJson('/api/v1/search?q=walking&types=not-a-real-type');

    $response->assertOk();
    expect($response->json('results.pages'))->toHaveCount(1);
});

it('offset pages through a single type without duplicating results', function () {
    Page::factory()->count(5)->published()->sequence(fn ($seq) => ['title' => "Walking Rule {$seq->index}"])->create(['content' => 'walking rules']);

    $first = $this->getJson('/api/v1/search?q=walking&types=pages&limit=2&offset=0');
    $second = $this->getJson('/api/v1/search?q=walking&types=pages&limit=2&offset=2');

    $first->assertOk();
    $second->assertOk();

    $firstIds = collect($first->json('results.pages'))->pluck('id');
    $secondIds = collect($second->json('results.pages'))->pluck('id');

    expect($firstIds)->toHaveCount(2);
    expect($secondIds)->toHaveCount(2);
    expect($firstIds->intersect($secondIds))->toBeEmpty();
});
