<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\ErrataResource;
use App\Models\Errata;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Errata
 */
class ErrataController extends Controller
{
    /**
     * List published errata
     *
     * Returns a paginated list of published errata entries.
     *
     * @queryParam search string Case-insensitive match against the errata title. Example: line of sight
     * @queryParam per_page int Results per page (max 100). Example: 25
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $errata = Errata::query()
            ->with('batch')
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->when($request->query('search'), fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return ErrataResource::collection($errata);
    }

    /**
     * Get a single errata entry
     *
     * Returns a single published errata entry by its slug.
     */
    public function show(Errata $errata): ErrataResource
    {
        abort_if($errata->published_at === null || $errata->newest !== null, 404);

        $errata->load('batch');

        return new ErrataResource($errata);
    }
}
