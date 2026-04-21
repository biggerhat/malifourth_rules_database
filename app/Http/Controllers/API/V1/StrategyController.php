<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\SuitEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\StrategyResource;
use App\Models\Strategy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Gaining Grounds
 */
class StrategyController extends Controller
{
    /**
     * List published strategies
     *
     * Returns a paginated list of published Gaining Grounds strategies.
     *
     * @queryParam season string Filter by season slug. Example: gaining-grounds-5
     * @queryParam suit string Filter by suit (rams, crows, masks, tomes). Example: masks
     * @queryParam per_page int Results per page (max 100). Example: 25
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $suit = SuitEnum::tryFrom((string) $request->query('suit', ''));

        $strategies = Strategy::query()
            ->with('season')
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->when($suit, fn ($q, $s) => $q->where('suit', $s))
            ->when(
                $request->query('season'),
                fn ($q, $slug) => $q->whereHas('season', fn ($sq) => $sq->where('slug', $slug))
            )
            ->orderBy('title')
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return StrategyResource::collection($strategies);
    }

    /**
     * Get a single strategy
     *
     * Returns a single published strategy by its slug.
     */
    public function show(Strategy $strategy): StrategyResource
    {
        abort_if($strategy->published_at === null || $strategy->newest !== null, 404);

        $strategy->load('season');

        return new StrategyResource($strategy);
    }
}
