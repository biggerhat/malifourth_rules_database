<?php

namespace App\Services\ContentBuilder;

/**
 * Shared across a single top-level ContentBuilder::getFullyHydratedContent() call tree so that
 * recursive tag resolution (e.g. a Page linking to a Section that links back to the same Page)
 * has a hard recursion limit and doesn't re-query the same slug twice at the same depth.
 *
 * MAX_DEPTH alone only bounds how many hops deep resolution goes, not how much work happens at
 * each hop: a hub page that transitively cross-references a large slice of the site's content can
 * still fan out into thousands of full parse/hydrate operations within that depth and blow the
 * request's execution-time limit. MAX_TOTAL_RESOLVED caps total items resolved across the whole
 * tree as a second, breadth-oriented backstop.
 */
class ContentHydrationContext
{
    public const MAX_DEPTH = 5;

    public const MAX_TOTAL_RESOLVED = 250;

    /** @var array<int, array<string, array<string, mixed>>> */
    public array $resolved = [];

    public int $totalResolved = 0;
}
