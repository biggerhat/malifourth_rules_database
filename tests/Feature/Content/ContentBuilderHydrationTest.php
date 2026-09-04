<?php

use App\Models\Page;
use App\Services\ContentBuilder\ContentBuilder;
use App\Services\ContentBuilder\ContentHydrationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('resolves a simple cross-page reference without truncation', function () {
    $target = Page::factory()->published()->create(['title' => 'Movement', 'content' => 'Rules text.']);
    $source = Page::factory()->published()->create([
        'title' => 'Combat',
        'content' => "{{pageLink={$target->slug}}}see movement{{/pageLink}}",
    ]);

    $hydrated = (new ContentBuilder($source->fresh()->content))->getFullyHydratedContent();

    expect($hydrated[0]['pageLink']['title'])->toBe('Movement');
});

it('does not infinitely recurse when two pages link back to each other', function () {
    $pageB = Page::factory()->published()->create(['title' => 'Page B', 'content' => 'placeholder']);
    $pageA = Page::factory()->published()->create([
        'title' => 'Page A',
        'content' => "{{pageLink={$pageB->slug}}}to B{{/pageLink}}",
    ]);
    $pageB->update(['content' => "{{pageLink={$pageA->slug}}}to A{{/pageLink}}"]);

    $start = microtime(true);
    $hydrated = (new ContentBuilder($pageA->fresh()->content))->getFullyHydratedContent();
    $elapsed = microtime(true) - $start;

    expect($elapsed)->toBeLessThan(5.0);

    // Walk the pageLink->content chain down to where the depth guard should have cut it off.
    $node = $hydrated[0]['pageLink'];
    for ($i = 0; $i < ContentHydrationContext::MAX_DEPTH; $i++) {
        expect($node['content'])->not->toBe([]);
        $node = $node['content'][0]['pageLink'];
    }

    // At the depth limit, nested content stops being hydrated rather than recursing further.
    expect($node['content'])->toBe([]);
});

it('does not re-query a slug already resolved by a sibling at the same depth', function () {
    $target = Page::factory()->published()->create(['title' => 'Movement', 'content' => 'Rules text.']);
    $branchOne = Page::factory()->published()->create([
        'title' => 'Branch One',
        'content' => "{{pageLink={$target->slug}}}see movement{{/pageLink}}",
    ]);
    $branchTwo = Page::factory()->published()->create([
        'title' => 'Branch Two',
        'content' => "{{pageLink={$target->slug}}}see movement{{/pageLink}}",
    ]);
    $root = Page::factory()->published()->create([
        'title' => 'Root',
        'content' => "{{pageLink={$branchOne->slug}}}one{{/pageLink}} {{pageLink={$branchTwo->slug}}}two{{/pageLink}}",
    ]);

    $rootContent = $root->fresh()->content;

    DB::enableQueryLog();
    (new ContentBuilder($rootContent))->getFullyHydratedContent();
    $pageQueries = collect(DB::getQueryLog())
        ->filter(fn ($q) => str_contains($q['query'], '"pages"') || str_contains($q['query'], '`pages`'));
    DB::disableQueryLog();

    // 1 query resolves [branchOne, branchTwo] at depth 0, and 1 more resolves [target] once at
    // depth 1 — not twice, even though both branches reference it.
    expect($pageQueries->count())->toBe(2);
});
