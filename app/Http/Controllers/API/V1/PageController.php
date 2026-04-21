<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Pages
 */
class PageController extends Controller
{
    /**
     * List published rules pages
     *
     * Returns a paginated list of published rules pages ordered by page number.
     *
     * @queryParam search string Case-insensitive match against the page title. Example: line of sight
     * @queryParam per_page int Results per page (max 100). Example: 25
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $pages = Page::query()
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->when($request->query('search'), fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderBy('page_number')
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return PageResource::collection($pages);
    }

    /**
     * Get a single rules page
     *
     * Returns a single published rules page by its slug.
     */
    public function show(Page $page): PageResource
    {
        abort_if($page->published_at === null || $page->newest !== null, 404);

        return new PageResource($page);
    }
}
