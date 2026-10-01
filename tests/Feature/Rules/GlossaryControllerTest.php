<?php

use App\Models\Index;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists published glossary entries alphabetically', function () {
    Index::factory()->published()->create(['title' => 'Zealot', 'type' => 'text']);
    Index::factory()->published()->create(['title' => 'Armor', 'type' => 'text']);

    $response = $this->get(route('rules.glossary'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('entries.0.title_text', 'Armor')
        ->where('entries.1.title_text', 'Zealot')
    );
});

it('does not list unpublished glossary entries', function () {
    Index::factory()->create(['title' => 'Draft Term', 'type' => 'text']);

    $response = $this->get(route('rules.glossary'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('entries', []));
});

it('does not list superseded versions of a glossary entry', function () {
    $original = Index::factory()->published()->create(['title' => 'Armor', 'type' => 'text']);

    $newest = Index::factory()->create([
        'title' => 'Armor',
        'type' => 'text',
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
        'published_by' => \App\Models\User::factory(),
    ]);
    $original->update(['newest' => $newest->id]);
    $original->delete();

    $response = $this->get(route('rules.glossary'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('entries', 1));
});
