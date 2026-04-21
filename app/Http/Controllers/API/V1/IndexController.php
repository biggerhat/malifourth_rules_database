<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\IndexTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\IndexResource;
use App\Models\Index;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Indices
 */
class IndexController extends Controller
{
    /**
     * List published indices
     *
     * Returns a paginated list of published indices, optionally filtered by type.
     *
     * @queryParam type string Filter by index type (image, text). Example: image
     * @queryParam search string Case-insensitive match against the index title. Example: concealment
     * @queryParam per_page int Results per page (max 100). Example: 25
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $type = IndexTypeEnum::tryFrom((string) $request->query('type', ''));

        $indices = Index::query()
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->when($type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->query('search'), fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderBy('title')
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return IndexResource::collection($indices);
    }

    /**
     * Get a single index entry
     *
     * Returns a single published index entry by its slug.
     */
    public function show(Index $index): IndexResource
    {
        abort_if($index->published_at === null || $index->newest !== null, 404);

        return new IndexResource($index);
    }
}
