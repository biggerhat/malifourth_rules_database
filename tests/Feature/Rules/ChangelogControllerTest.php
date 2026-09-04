<?php

use App\Models\CardErrata;
use App\Models\CardErrataEntry;
use App\Models\Errata;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists published errata, faq, and card errata entries ordered by most recent', function () {
    $older = Errata::factory()->published()->create(['title' => 'Older Errata']);
    $older->update(['published_at' => now()->subDays(5)]);

    $faq = Faq::factory()->published()->create(['title' => 'Newer FAQ']);

    $response = $this->get(route('changelog.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('entries.0.title', 'Newer FAQ')
        ->where('entries.1.title', 'Older Errata'));
});

it('excludes unpublished and superseded content from the changelog', function () {
    Errata::factory()->create(['title' => 'Draft Errata']);

    $original = Errata::factory()->published()->create(['title' => 'Movement Errata']);
    $publisher = User::factory()->create();
    $newest = Errata::factory()->create([
        'title' => 'Movement Errata',
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
        'published_by' => $publisher->id,
    ]);
    $original->update(['newest' => $newest->id]);
    $original->delete();

    $response = $this->get(route('changelog.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('entries', 1));
});

it('includes card errata with entry-derived excerpts', function () {
    $card = CardErrata::factory()->published()->create(['card_name' => 'Lady Justice']);
    CardErrataEntry::factory()->for($card)->create(['what_changed' => 'Adjusted her Df stat.']);

    $response = $this->get(route('changelog.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('entries.0.title', 'Lady Justice')
        ->where('entries.0.type', 'card_errata')
        ->where('entries.0.excerpt', 'Adjusted her Df stat.'));
});

it('serves a well-formed RSS feed of the same published entries', function () {
    Faq::factory()->published()->create(['title' => 'Line of Sight clarified']);

    $response = $this->get(route('changelog.feed'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');

    $xml = simplexml_load_string($response->getContent());
    expect($xml)->not->toBeFalse();
    expect((string) $xml->channel->item[0]->title)->toContain('Line of Sight clarified');
});
