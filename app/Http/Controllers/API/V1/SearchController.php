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
use Illuminate\Support\Facades\DB;

/**
 * @tags Search
 */
class SearchController extends Controller
{
    /**
     * @var array<string, class-string<Model>>
     */
    private const MODELS = [
        'pages' => Page::class,
        'sections' => Section::class,
        'indices' => Index::class,
        'faqs' => Faq::class,
        'errata' => Errata::class,
        'seasons' => Season::class,
        'strategies' => Strategy::class,
        'schemes' => Scheme::class,
        'season_pages' => SeasonPage::class,
    ];

    /**
     * Search published content
     *
     * Performs a full-text (or, where unsupported, case-insensitive substring) search
     * across all public content types, returning matches grouped by type with a short
     * snippet around the first hit. Terms can be quoted to match as a phrase: `"line of sight"`.
     *
     * @queryParam q string required The search query. Example: line of sight
     * @queryParam types string Comma-separated list of content types to search. Omit to search all types. Allowed values: pages, sections, indices, faqs, errata, seasons, strategies, schemes, season_pages. Example: pages,faqs
     * @queryParam limit int Max results per content type (default 20, max 100). Example: 20
     * @queryParam offset int Number of results to skip per content type, for paging through a single type's results. Example: 0
     */
    public function __invoke(Request $request): JsonResponse
    {
        $queryString = (string) $request->query('q', '');
        $terms = $this->parseQueryString($queryString);
        $limit = min((int) $request->query('limit', 20), 100);
        $offset = max(0, (int) $request->query('offset', 0));
        $types = $this->parseTypes($request->query('types'));

        if ($terms === []) {
            return response()->json([
                'query' => $queryString,
                'terms' => [],
                'results' => array_fill_keys(array_keys(self::MODELS), []),
            ]);
        }

        $results = [];
        foreach (self::MODELS as $key => $model) {
            $results[$key] = in_array($key, $types, true)
                ? $this->collect($model, $terms, $limit, $offset, $key === 'season_pages' ? ['season'] : [])
                : [];
        }

        return response()->json([
            'query' => $queryString,
            'terms' => $terms,
            'results' => $results,
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function parseTypes(mixed $raw): array
    {
        $allTypes = array_keys(self::MODELS);

        if (empty($raw)) {
            return $allTypes;
        }

        $requested = is_array($raw) ? $raw : explode(',', (string) $raw);
        $valid = array_values(array_intersect($allTypes, $requested));

        return $valid === [] ? $allTypes : $valid;
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<int, string>  $terms
     * @param  array<int, string>  $with
     * @return array<int, array<string, mixed>>
     */
    private function collect(string $model, array $terms, int $limit, int $offset, array $with = []): array
    {
        $query = $model::query()
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->when($with !== [], fn (Builder $q) => $q->with($with));

        return $this->applySearchTerms($query, $terms)
            ->offset($offset)
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
     * @param  array<int, string>  $terms
     */
    private function applySearchTerms(Builder $query, array $terms): Builder
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $booleanQuery = collect($terms)
                ->map(fn ($term) => str_contains($term, ' ') ? '+"'.$term.'"' : '+'.$term)
                ->implode(' ');

            return $query
                ->whereFullText(['title', 'searchable_text'], $booleanQuery, ['mode' => 'boolean'])
                ->orderByRaw('MATCH(title, searchable_text) AGAINST(? IN BOOLEAN MODE) DESC', [$booleanQuery]);
        }

        return $query->where(function (Builder $q) use ($terms) {
            foreach ($terms as $term) {
                $q->where(function (Builder $sub) use ($term) {
                    $sub->where('title', 'LIKE', "%{$term}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$term}%");
                });
            }
        });
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
