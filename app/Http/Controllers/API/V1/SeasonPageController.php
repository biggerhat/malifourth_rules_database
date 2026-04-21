<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\SeasonPageResource;
use App\Models\SeasonPage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Gaining Grounds
 */
class SeasonPageController extends Controller
{
    /**
     * List published season pages
     *
     * Returns a paginated list of published season pages.
     *
     * @queryParam season string Filter by season slug. Example: gaining-grounds-5
     * @queryParam per_page int Results per page (max 100). Example: 25
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $seasonPages = SeasonPage::query()
            ->with('season')
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->when(
                $request->query('season'),
                fn ($q, $slug) => $q->whereHas('season', fn ($sq) => $sq->where('slug', $slug))
            )
            ->orderBy('sort_order')
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return SeasonPageResource::collection($seasonPages);
    }

    /**
     * Get a single season page
     *
     * Returns a single published season page by its slug.
     */
    public function show(SeasonPage $seasonPage): SeasonPageResource
    {
        abort_if($seasonPage->published_at === null || $seasonPage->newest !== null, 404);

        $seasonPage->load('season');

        return new SeasonPageResource($seasonPage);
    }
}
