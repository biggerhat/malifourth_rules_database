<?php

use App\Models\CardErrata;
use App\Models\Page;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists published content urls in the sitemap and excludes unpublished content', function () {
    $page = Page::factory()->published()->create(['page_number' => 1]);
    Page::factory()->create(['page_number' => 2]); // unpublished, should be excluded

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('application/xml');
    $response->assertSee(route('rules.page.view', $page->slug), false);
});

it('excludes superseded versions from the sitemap', function () {
    $original = Section::factory()->published()->create();
    $newest = Section::factory()->create([
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
    ]);
    $original->update(['newest' => $newest->id]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertDontSee(route('rules.section.view', $original->slug), false);
    $response->assertSee(route('rules.section.view', $newest->slug), false);
});

it('includes published card errata urls in the sitemap', function () {
    $card = CardErrata::factory()->published()->create();
    CardErrata::factory()->create(); // unpublished, should be excluded

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertSee(route('errata.cards.view', $card->slug), false);
});

it('serves a robots.txt that points at the sitemap', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk();
    $response->assertSee(route('sitemap'), false);
});
