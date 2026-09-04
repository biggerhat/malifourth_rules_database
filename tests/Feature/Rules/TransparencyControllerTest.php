<?php

use App\Models\Errata;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows who approved and published currently-live content', function () {
    $approver = User::factory()->create(['name' => 'Ana Approver']);
    $publisher = User::factory()->create(['name' => 'Pete Publisher']);

    $page = Page::factory()->create(['title' => 'Movement', 'published_at' => now(), 'published_by' => $publisher->id]);
    $page->approval()->create([
        'initiated_by' => $publisher->id,
        'approved_by' => $approver->id,
        'approved_at' => now(),
        'change_notes' => 'Clarified diagonal movement.',
        'internal_notes' => 'Reviewer flagged wording on first pass, do not publish this note.',
    ]);

    $response = $this->get(route('transparency.index'));

    $response->assertOk();
    $response->assertInertia(fn ($assert) => $assert
        ->where('entries.0.title', 'Movement')
        ->where('entries.0.approved_by', 'Ana Approver')
        ->where('entries.0.published_by', 'Pete Publisher')
        ->where('entries.0.change_notes', 'Clarified diagonal movement.')
    );

    $response->assertDontSee('Reviewer flagged wording', false);
    $response->assertDontSeeText('Reviewer flagged wording on first pass');
});

it('never exposes the internal_notes key anywhere in the page payload', function () {
    $page = Page::factory()->published()->create(['title' => 'Movement']);
    $page->approval()->create([
        'initiated_by' => $page->published_by,
        'approved_by' => $page->published_by,
        'approved_at' => now(),
        'change_notes' => 'Public change note.',
        'internal_notes' => 'Secret internal note.',
    ]);

    $response = $this->get(route('transparency.index'));

    $response->assertOk();
    $response->assertDontSeeText('Secret internal note');
    $response->assertInertia(fn ($assert) => $assert->has('entries.0', fn ($entry) => $entry
        ->missing('internal_notes')
        ->etc()
    ));
});

it('does not show unpublished or superseded content', function () {
    Page::factory()->create(['title' => 'Draft Page']);

    $original = Errata::factory()->published()->create(['title' => 'Old Errata Title']);
    $newest = Errata::factory()->create([
        'title' => 'New Errata Title',
        'previous' => $original->id,
        'original' => $original->id,
        'published_at' => now(),
        'published_by' => User::factory(),
    ]);
    $original->update(['newest' => $newest->id]);
    $original->delete();

    $response = $this->get(route('transparency.index'));

    $response->assertOk();
    $response->assertInertia(fn ($assert) => $assert
        ->where('pagination.total', 1)
        ->where('entries.0.title', 'New Errata Title')
    );
});

it('orders entries by published date, most recent first', function () {
    Page::factory()->published()->create(['title' => 'Older Page', 'published_at' => now()->subDays(2)]);
    Page::factory()->published()->create(['title' => 'Newer Page', 'published_at' => now()]);

    $response = $this->get(route('transparency.index'));

    $response->assertOk();
    $response->assertInertia(fn ($assert) => $assert
        ->where('entries.0.title', 'Newer Page')
        ->where('entries.1.title', 'Older Page')
    );
});
