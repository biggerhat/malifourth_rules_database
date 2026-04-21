<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\SchemeResource;
use App\Models\Scheme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Gaining Grounds
 */
class SchemeController extends Controller
{
    /**
     * List published schemes
     *
     * Returns a paginated list of published Gaining Grounds schemes.
     *
     * @queryParam season string Filter by season slug. Example: gaining-grounds-5
     * @queryParam per_page int Results per page (max 100). Example: 25
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $schemes = Scheme::query()
            ->with('season')
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->when(
                $request->query('season'),
                fn ($q, $slug) => $q->whereHas('season', fn ($sq) => $sq->where('slug', $slug))
            )
            ->orderBy('title')
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return SchemeResource::collection($schemes);
    }

    /**
     * Get a single scheme
     *
     * Returns a single published scheme by its slug, including references to possible next schemes.
     */
    public function show(Scheme $scheme): SchemeResource
    {
        abort_if($scheme->published_at === null || $scheme->newest !== null, 404);

        $scheme->load(['season', 'nextScheme1', 'nextScheme2', 'nextScheme3']);

        return new SchemeResource($scheme);
    }
}
