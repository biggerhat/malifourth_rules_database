<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Errata;
use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Scheme;
use App\Models\Season;
use App\Models\SeasonPage;
use App\Models\Section;
use App\Models\Strategy;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Search
 */
class SearchController extends Controller
{
    /**
     * Search published content
     *
     * Performs a case-insensitive substring search across all public content types,
     * returning matches grouped by type with a short snippet around the first hit.
     * Terms can be quoted to match as a phrase: `"line of sight"`.
     *
     * @queryParam q string required The search query. Example: line of sight
     * @queryParam limit int Max results per content type (default 20, max 100). Example: 20
     */
    public function __invoke(Request $request): JsonResponse
    {
        $queryString = (string) $request->query('q', '');
        $terms = $this->parseQueryString($queryString);
        $limit = min((int) $request->query('limit', 20), 100);

        if ($terms === []) {
            return response()->json([
                'query' => $queryString,
                'terms' => [],
                'results' => array_fill_keys(
                    ['pages', 'sections', 'indices', 'faqs', 'errata', 'seasons', 'strategies', 'schemes', 'season_pages'],
                    []
                ),
            ]);
        }

        $results = [
            'pages' => $this->collect(Page::class, $terms, $limit),
            'sections' => $this->collect(Section::class, $terms, $limit),
            'indices' => $this->collect(Index::class, $terms, $limit),
            'faqs' => $this->collect(Faq::class, $terms, $limit),
            'errata' => $this->collect(Errata::class, $terms, $limit),
            'seasons' => $this->collect(Season::class, $terms, $limit),
            'strategies' => $this->collect(Strategy::class, $terms, $limit),
            'schemes' => $this->collect(Scheme::class, $terms, $limit),
            'season_pages' => $this->collect(SeasonPage::class, $terms, $limit, ['season']),
        ];

        return response()->json([
            'query' => $queryString,
            'terms' => $terms,
            'results' => $results,
        ]);
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<int, string>  $terms
     * @param  array<int, string>  $with
     * @return array<int, array<string, mixed>>
     */
    private function collect(string $model, array $terms, int $limit, array $with = []): array
    {
        return $model::query()
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->when($with !== [], fn (Builder $q) => $q->with($with))
            ->where(function (Builder $q) use ($terms) {
                foreach ($terms as $term) {
                    $q->where(function (Builder $sub) use ($term) {
                        $sub->where('title', 'LIKE', "%{$term}%")
                            ->orWhere('searchable_text', 'LIKE', "%{$term}%");
                    });
                }
            })
            ->limit($limit)
            ->get()
            ->map(fn (Model $row) => [
                'id' => $row->id,
                'title' => ContentBuilder::toPlainText($row->title ?? ''),
                'slug' => $row->slug,
                'season_slug' => $row->relationLoaded('season') ? $row->season?->slug : null,
                'snippet' => $this->buildSnippet($row->searchable_text ?? '', $terms),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function parseQueryString(string $query): array
    {
        if (trim($query) === '') {
            return [];
        }

        preg_match_all('/"([^"]+)"|\S+/', $query, $matches);

        return array_values(array_filter(array_map(
            fn ($m1, $m2) => $m1 !== '' ? $m1 : $m2,
            $matches[1],
            $matches[0],
        )));
    }

    /**
     * @param  array<int, string>  $terms
     */
    private function buildSnippet(string $text, array $terms): ?string
    {
        if ($text === '' || $terms === []) {
            return null;
        }

        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $lower = mb_strtolower($text);

        $position = null;
        foreach ($terms as $term) {
            $pos = mb_strpos($lower, mb_strtolower($term));
            if ($pos !== false) {
                $position = $pos;
                break;
            }
        }

        if ($position === null) {
            return null;
        }

        $window = 120;
        $start = max(0, $position - (int) ($window / 2));
        $end = min(mb_strlen($text), $start + $window);

        $snippet = mb_substr($text, $start, $end - $start);

        if ($start > 0) {
            $snippet = '...'.$snippet;
        }
        if ($end < mb_strlen($text)) {
            $snippet .= '...';
        }

        return $snippet;
    }
}
