<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\BatchResource;
use App\Models\Batch;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Batches
 */
class BatchController extends Controller
{
    /**
     * List public batches
     *
     * Returns a paginated list of public, published batches (release bundles) newest first.
     *
     * @queryParam per_page int Results per page (max 100). Example: 25
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $batches = Batch::query()
            ->where('is_public', true)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return BatchResource::collection($batches);
    }

    /**
     * Get a single batch
     *
     * Returns a single public batch by its slug, including all published content that ships with it.
     */
    public function show(Batch $batch): BatchResource
    {
        abort_if(! $batch->is_public || $batch->published_at === null, 404);

        $publishedOnly = fn (HasMany $q) => $q->whereNotNull('published_at')->whereNull('newest');

        $batch->load([
            'errata' => $publishedOnly,
            'faqs' => $publishedOnly,
            'indices' => $publishedOnly,
            'pages' => $publishedOnly,
            'sections' => $publishedOnly,
            'schemes' => $publishedOnly,
            'seasons' => $publishedOnly,
            'seasonPages' => $publishedOnly,
            'strategies' => $publishedOnly,
        ]);

        return new BatchResource($batch);
    }
}
