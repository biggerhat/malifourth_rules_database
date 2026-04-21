<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\FaqCategoryEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags FAQs
 */
class FaqController extends Controller
{
    /**
     * List published FAQs
     *
     * Returns a paginated list of published FAQs, optionally filtered by category or title.
     *
     * @queryParam category string Filter by category slug. Example: general
     * @queryParam search string Case-insensitive match against the FAQ title. Example: line of sight
     * @queryParam per_page int Results per page (max 100). Example: 25
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $category = FaqCategoryEnum::tryFrom((string) $request->query('category', ''));

        $faqs = Faq::query()
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->when($category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->query('search'), fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return FaqResource::collection($faqs);
    }

    /**
     * Get a single FAQ
     *
     * Returns a single published FAQ by its slug.
     */
    public function show(Faq $faq): FaqResource
    {
        abort_if($faq->published_at === null || $faq->newest !== null, 404);

        return new FaqResource($faq);
    }
}
