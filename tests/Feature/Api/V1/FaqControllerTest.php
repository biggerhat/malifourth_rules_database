<?php

use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published, current faqs', function () {
    Faq::factory()->published()->create(['title' => 'Published FAQ']);
    Faq::factory()->create(['title' => 'Draft FAQ']);

    $response = $this->getJson('/api/v1/faqs');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Published FAQ');
});

it('excludes superseded faq versions from the list', function () {
    $publisher = User::factory()->create();
    $original = Faq::factory()->published()->create(['title' => 'Old']);
    $newest = Faq::factory()->create([
        'title' => 'New',
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
        'published_by' => $publisher->id,
    ]);
    $original->update(['newest' => $newest->id]);

    $response = $this->getJson('/api/v1/faqs');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('New');
});

it('returns a single published faq by slug', function () {
    $faq = Faq::factory()->published()->create(['answer' => '<p>Yes, always.</p>']);

    $response = $this->getJson("/api/v1/faqs/{$faq->slug}");

    $response->assertOk()->assertJsonPath('data.slug', $faq->slug);
});

it('returns 404 for an unpublished faq', function () {
    $faq = Faq::factory()->create();

    $this->getJson("/api/v1/faqs/{$faq->slug}")->assertStatus(404);
});

it('paginates the faq list respecting per_page', function () {
    Faq::factory()->count(3)->published()->create();

    $response = $this->getJson('/api/v1/faqs?per_page=2');

    $response->assertOk()->assertJsonCount(2, 'data');
    expect($response->json('meta.per_page'))->toBe(2);
});
