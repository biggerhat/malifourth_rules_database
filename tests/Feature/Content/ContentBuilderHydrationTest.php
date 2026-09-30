<?php

use App\Models\Page;
use App\Models\User;
use App\Services\ContentBuilder\ContentBuilder;
use App\Services\ContentBuilder\ContentHydrationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

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

it('caps total items hydrated across the whole tree, not just per depth', function () {
    Queue::fake();

    $user = User::factory()->create();
    $leafCount = ContentHydrationContext::MAX_TOTAL_RESOLVED + 20;

    // Bypass the factory here (its page_number faker only has a 1-100 unique range) — a wide,
    // non-cyclic fan-out of distinct pages under one branch is what the total-item budget guards
    // against, since the depth cap alone doesn't limit how much work happens at a single depth.
    $leaves = collect(range(0, $leafCount - 1))->map(fn ($i) => Page::create([
        'title' => "Leaf {$i}",
        'content' => "Rules text {$i}.",
        'page_number' => 1000 + $i,
        'published_at' => now(),
        'published_by' => $user->id,
    ]));

    $branchContent = $leaves->map(fn ($leaf) => "{{pageLink={$leaf->slug}}}leaf{{/pageLink}}")->implode('');
    $branch = Page::create([
        'title' => 'Branch',
        'content' => $branchContent,
        'page_number' => 999,
        'published_at' => now(),
        'published_by' => $user->id,
    ]);
    $root = Page::create([
        'title' => 'Root',
        'content' => "{{pageLink={$branch->slug}}}branch{{/pageLink}}",
        'page_number' => 998,
        'published_at' => now(),
        'published_by' => $user->id,
    ]);

    $hydrated = (new ContentBuilder($root->fresh()->content))->getFullyHydratedContent();
    $leafContents = collect($hydrated[0]['pageLink']['content'])->pluck('pageLink.content');

    // Some leaves fall within the budget and get their own content hydrated; once the shared
    // budget is exhausted, the rest are truncated to [] even though they're all at the same depth.
    expect($leafContents->filter(fn ($c) => $c !== [])->count())
        ->toBeLessThanOrEqual(ContentHydrationContext::MAX_TOTAL_RESOLVED)
        ->toBeGreaterThan(0);
    expect($leafContents->filter(fn ($c) => $c === [])->count())->toBeGreaterThan(0);
});
