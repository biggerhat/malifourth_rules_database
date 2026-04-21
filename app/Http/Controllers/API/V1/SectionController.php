<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\SectionResource;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Sections
 */
class SectionController extends Controller
{
    /**
     * List published sections
     *
     * Returns a paginated list of published rules sections.
     *
     * @queryParam search string Case-insensitive match against the section title. Example: terrain
     * @queryParam per_page int Results per page (max 100). Example: 25
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $sections = Section::query()
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->when($request->query('search'), fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderBy('title')
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return SectionResource::collection($sections);
    }

    /**
     * Get a single section
     *
     * Returns a single published section by its slug.
     */
    public function show(Section $section): SectionResource
    {
        abort_if($section->published_at === null || $section->newest !== null, 404);

        return new SectionResource($section);
    }
}
