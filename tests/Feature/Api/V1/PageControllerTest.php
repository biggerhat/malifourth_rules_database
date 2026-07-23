<?php

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only published, current pages ordered by page number', function () {
    Page::factory()->published()->create(['title' => 'Second', 'page_number' => 2]);
    Page::factory()->published()->create(['title' => 'First', 'page_number' => 1]);
    Page::factory()->create(['title' => 'Draft', 'page_number' => 3]);

    $response = $this->getJson('/api/v1/pages');

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
    expect($response->json('data.0.title'))->toBe('First');
    expect($response->json('data.1.title'))->toBe('Second');
});

it('excludes superseded versions from the api list, exposing only the newest', function () {
    $publisher = User::factory()->create();
    $original = Page::factory()->published()->create(['title' => 'Old Title', 'page_number' => 1]);
    $newest = Page::factory()->create([
        'title' => 'New Title',
        'page_number' => 1,
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
        'published_by' => $publisher->id,
    ]);
    $original->update(['newest' => $newest->id]);

    $response = $this->getJson('/api/v1/pages');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('New Title');
});

it('returns page content in the show response', function () {
    $page = Page::factory()->published()->create(['content' => '<p>Look down the barrel.</p>']);

    $response = $this->getJson("/api/v1/pages/{$page->slug}");

    $response->assertOk();
    $response->assertJsonPath('data.content', '<p>Look down the barrel.</p>');
    $response->assertJsonPath('data.slug', $page->slug);
});

it('returns 404 for an unpublished page', function () {
    $page = Page::factory()->create();

    $this->getJson("/api/v1/pages/{$page->slug}")->assertStatus(404);
});

it('filters the list by a case-insensitive title search', function () {
    Page::factory()->published()->create(['title' => 'Line of Sight', 'page_number' => 1]);
    Page::factory()->published()->create(['title' => 'Movement', 'page_number' => 2]);

    $response = $this->getJson('/api/v1/pages?search=line');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.title'))->toBe('Line of Sight');
});
