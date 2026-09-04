<?php

use App\Models\Faq;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns published pages matching the query', function () {
    Page::factory()->published()->create(['title' => 'Movement', 'content' => 'Rules about walking and running.']);

    $response = $this->get(route('search', ['q' => 'walking']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('results.0.title', 'Movement')->where('results.0.type', 'pages'));
});

it('does not return unpublished or unapproved pages in search results', function () {
    Page::factory()->create(['title' => 'Draft Rule', 'content' => 'Secret unreleased walking mechanic.']);

    $response = $this->get(route('search', ['q' => 'walking']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('results', [])->where('pagination.total', 0));
});

it('does not return superseded versions of a page in search results', function () {
    $original = Page::factory()->published()->create(['title' => 'Movement', 'content' => 'Old walking rules text']);

    $newest = Page::factory()->create([
        'title' => 'Movement',
        'content' => 'New walking rules text',
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
        'published_by' => User::factory(),
    ]);
    $original->update(['newest' => $newest->id]);
    $original->delete();

    $response = $this->get(route('search', ['q' => 'walking']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('results.0.title', 'Movement')->where('pagination.total', 1));
});

it('excludes a content type from results and counts when its filter is unchecked', function () {
    Page::factory()->published()->create(['title' => 'Movement', 'content' => 'walking rules']);
    Faq::factory()->published()->create(['title' => 'Walking FAQ', 'answer' => 'walking question and answer']);

    $response = $this->get(route('search', ['q' => 'walking', 'types' => 'faqs']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('selectedTypes', ['faqs'])
        ->where('counts.pages', 0)
        ->where('counts.faqs', 1)
        ->where('pagination.total', 1)
        ->where('results.0.type', 'faqs')
    );
});

it('an empty types filter is treated as searching every type', function () {
    Page::factory()->published()->create(['title' => 'Movement', 'content' => 'walking rules']);

    $response = $this->get(route('search', ['q' => 'walking', 'types' => 'not-a-real-type']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('pagination.total', 1));
});

it('paginates search results without duplicating items across pages', function () {
    Page::factory()->count(25)->published()->sequence(fn ($seq) => ['title' => "Walking Rule {$seq->index}"])->create(['content' => 'walking rules']);

    $pageOne = $this->get(route('search', ['q' => 'walking', 'page' => 1]));
    $pageTwo = $this->get(route('search', ['q' => 'walking', 'page' => 2]));

    $pageOne->assertOk();
    $pageTwo->assertOk();

    $pageOneTitles = $pageOne->viewData('page')['props']['results'];
    $pageTwoTitles = $pageTwo->viewData('page')['props']['results'];

    expect($pageOneTitles)->toHaveCount(20);
    expect($pageTwoTitles)->toHaveCount(5);

    $pageOneIds = collect($pageOneTitles)->pluck('id');
    $pageTwoIds = collect($pageTwoTitles)->pluck('id');
    expect($pageOneIds->intersect($pageTwoIds))->toBeEmpty();
});
