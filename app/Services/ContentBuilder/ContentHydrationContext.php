<?php

namespace App\Services\ContentBuilder;

/**
 * Shared across a single top-level ContentBuilder::getFullyHydratedContent() call tree so that
 * recursive tag resolution (e.g. a Page linking to a Section that links back to the same Page)
 * has a hard recursion limit and doesn't re-query the same slug twice at the same depth.
 */
class ContentHydrationContext
{
    public const MAX_DEPTH = 5;

    /** @var array<int, array<string, array<string, mixed>>> */
    public array $resolved = [];
}
