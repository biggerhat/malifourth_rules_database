<?php

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the lowest-numbered published page on the rules index', function () {
    Page::factory()->published()->create(['title' => 'First Page', 'page_number' => 1]);
    Page::factory()->published()->create(['title' => 'Second Page', 'page_number' => 2]);

    $response = $this->get(route('rules.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('page_number', 1)->where('slug', fn ($slug) => str_ends_with($slug, 'first-page')));
});

it('returns 404 for the rules index when there are no published pages', function () {
    Page::factory()->create(['page_number' => 1]);

    $this->get(route('rules.index'))->assertStatus(404);
});

it('renders a single published page by slug', function () {
    $page = Page::factory()->published()->create(['title' => 'Movement']);

    $this->get(route('rules.page.view', $page))->assertOk();
});

it('returns 404 when viewing an unpublished page', function () {
    $page = Page::factory()->create(['title' => 'Draft Page']);

    $this->get(route('rules.page.view', $page))->assertStatus(404);
});

it('redirects the history route to the live page once it is the newest version', function () {
    $page = Page::factory()->published()->create(['title' => 'Movement']);

    $this->get(route('rules.page.history', $page))->assertRedirect(route('rules.page.view', $page->slug));
});

it('shows a superseded version on the history route without exposing it on the live route', function () {
    $original = Page::factory()->published()->create(['title' => 'Movement', 'content' => 'Old rules text']);
    $publisher = User::factory()->create();

    $newest = Page::factory()->create([
        'title' => 'Movement',
        'content' => 'New rules text',
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
        'published_by' => $publisher->id,
    ]);
    $original->update(['newest' => $newest->id]);
    $original->delete();

    $historyResponse = $this->get(route('rules.page.history', $original->slug));
    $historyResponse->assertOk();
    $historyResponse->assertInertia(fn ($page) => $page->where('viewing_old_version', true));

    $this->get(route('rules.page.view', $newest))->assertOk();
});
