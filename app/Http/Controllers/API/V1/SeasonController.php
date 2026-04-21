<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\SeasonResource;
use App\Models\Season;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Gaining Grounds
 */
class SeasonController extends Controller
{
    /**
     * List published Gaining Grounds seasons
     *
     * Returns a paginated list of published seasons.
     *
     * @queryParam per_page int Results per page (max 100). Example: 25
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $seasons = Season::query()
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->orderByDesc('published_at')
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return SeasonResource::collection($seasons);
    }

    /**
     * Get a single season
     *
     * Returns a single published season with its strategies, schemes, and pages.
     */
    public function show(Season $season): SeasonResource
    {
        abort_if($season->published_at === null || $season->newest !== null, 404);

        $season->load([
            'strategies' => fn (HasMany $q) => $q->whereNotNull('published_at')->whereNull('newest')->orderBy('title'),
            'schemes' => fn (HasMany $q) => $q->whereNotNull('published_at')->whereNull('newest')->orderBy('title'),
            'seasonPages' => fn (HasMany $q) => $q->whereNotNull('published_at')->whereNull('newest')->orderBy('sort_order'),
        ]);

        return new SeasonResource($season);
    }
}
