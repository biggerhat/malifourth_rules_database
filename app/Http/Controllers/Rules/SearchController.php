<?php

namespace App\Http\Controllers\Rules;

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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    private const RESULT_LIMIT = 200;

    private const PER_PAGE = 20;

    /**
     * The content types this search covers, in display priority order.
     *
     * @var array<int, string>
     */
    private const TYPES = [
        'pages', 'sections', 'indices', 'faqs',
        'seasons', 'strategies', 'season_pages', 'schemes', 'errata',
    ];

    public function view(Request $request)
    {
        $queryString = $request->input('q');
        $queryParameters = $this->parseQueryString($queryString);
        $selectedTypes = $this->parseTypes($request->input('types'));
        $page = max(1, (int) $request->input('page', 1));

        $counts = [];
        $allItems = [];

        foreach ($this->typeDefinitions($queryParameters) as $type => $definition) {
            if (! in_array($type, $selectedTypes, true)) {
                $counts[$type] = 0;

                continue;
            }

            $items = $definition['query']()->limit(self::RESULT_LIMIT)->get()->map($definition['map'])->all();
            $counts[$type] = count($items);

            foreach ($items as $item) {
                $item['type'] = $type;
                $allItems[] = $item;
            }
        }

        $total = count($allItems);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $lastPage);
        $pagedItems = array_slice($allItems, ($page - 1) * self::PER_PAGE, self::PER_PAGE);

        return inertia('Search/Results', [
            'query' => $queryString,
            'queryTerms' => $queryParameters,
            'types' => self::TYPES,
            'selectedTypes' => $selectedTypes,
            'counts' => $counts,
            'results' => $pagedItems,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'total' => $total,
            ],
        ]);
    }

    /**
     * @param  array<int, string>  $queryParameters
     * @return array<string, array{query: \Closure(): Builder, map: \Closure}>
     */
    private function typeDefinitions(array $queryParameters): array
    {
        return [
            'pages' => [
                'query' => fn () => Page::query()->published()->whereNull('newest')->when($queryParameters, fn ($q) => $this->applySearchTerms($q, $queryParameters)),
                'map' => fn ($page) => [
                    'id' => $page->id,
                    'title' => ContentBuilder::parseTitleTags($page->title),
                    'slug' => $page->slug,
                    'snippet' => $this->buildSnippet($page->searchable_text ?? '', $queryParameters),
                ],
            ],
            'sections' => [
                'query' => fn () => Section::query()->published()->whereNull('newest')->when($queryParameters, fn ($q) => $this->applySearchTerms($q, $queryParameters)),
                'map' => fn ($section) => [
                    'id' => $section->id,
                    'title' => ContentBuilder::parseTitleTags($section->title),
                    'slug' => $section->slug,
                    'snippet' => $this->buildSnippet($section->searchable_text ?? '', $queryParameters),
                ],
            ],
            'indices' => [
                'query' => fn () => Index::query()->published()->whereNull('newest')->when($queryParameters, fn ($q) => $this->applySearchTerms($q, $queryParameters)),
                'map' => fn ($index) => [
                    'id' => $index->id,
                    'title' => ContentBuilder::parseTitleTags($index->title),
                    'slug' => $index->slug,
                    'snippet' => $this->buildSnippet($index->searchable_text ?? '', $queryParameters),
                ],
            ],
            'faqs' => [
                'query' => fn () => Faq::query()->published()->whereNull('newest')->when($queryParameters, fn ($q) => $this->applySearchTerms($q, $queryParameters)),
                'map' => fn ($faq) => [
                    'id' => $faq->id,
                    'title' => ContentBuilder::toPlainText($faq->title),
                    'slug' => $faq->slug,
                    'snippet' => $this->buildSnippet($faq->searchable_text ?? '', $queryParameters),
                ],
            ],
            'seasons' => [
                'query' => fn () => Season::query()->published()->whereNull('newest')->when($queryParameters, fn ($q) => $this->applySearchTerms($q, $queryParameters)),
                'map' => fn ($season) => [
                    'id' => $season->id,
                    'title' => $season->title,
                    'slug' => $season->slug,
                    'snippet' => $this->buildSnippet($season->searchable_text ?? '', $queryParameters),
                ],
            ],
            'strategies' => [
                'query' => fn () => Strategy::query()->published()->whereNull('newest')->when($queryParameters, fn ($q) => $this->applySearchTerms($q, $queryParameters)),
                'map' => fn ($strategy) => [
                    'id' => $strategy->id,
                    'title' => $strategy->title,
                    'slug' => $strategy->slug,
                    'snippet' => $this->buildSnippet($strategy->searchable_text ?? '', $queryParameters),
                ],
            ],
            'season_pages' => [
                'query' => fn () => SeasonPage::query()->published()->whereNull('newest')->when($queryParameters, fn ($q) => $this->applySearchTerms($q, $queryParameters))->with('season'),
                'map' => fn ($sp) => [
                    'id' => $sp->id,
                    'title' => $sp->title,
                    'slug' => $sp->slug,
                    'season_slug' => $sp->season?->slug,
                    'snippet' => $this->buildSnippet($sp->searchable_text ?? '', $queryParameters),
                ],
            ],
            'schemes' => [
                'query' => fn () => Scheme::query()->published()->whereNull('newest')->when($queryParameters, fn ($q) => $this->applySearchTerms($q, $queryParameters)),
                'map' => fn ($scheme) => [
                    'id' => $scheme->id,
                    'title' => $scheme->title,
                    'slug' => $scheme->slug,
                    'snippet' => $this->buildSnippet($scheme->searchable_text ?? '', $queryParameters),
                ],
            ],
            'errata' => [
                'query' => fn () => Errata::query()->published()->whereNull('newest')->when($queryParameters, fn ($q) => $this->applySearchTerms($q, $queryParameters)),
                'map' => fn ($errata) => [
                    'id' => $errata->id,
                    'title' => $errata->title,
                    'slug' => $errata->slug,
                    'snippet' => $this->buildSnippet($errata->searchable_text ?? '', $queryParameters),
                ],
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function parseTypes(mixed $raw): array
    {
        if (empty($raw)) {
            return self::TYPES;
        }

        $requested = is_array($raw) ? $raw : explode(',', (string) $raw);
        $valid = array_values(array_intersect(self::TYPES, $requested));

        return $valid === [] ? self::TYPES : $valid;
    }

    private function parseQueryString(string $query): array
    {
        preg_match_all('/"([^"]+)"|\S+/', $query, $matches);

        return array_map(function ($m1, $m2) {
            return $m1 !== '' ? $m1 : $m2;
        }, $matches[1], $matches[0]);
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

        foreach ($terms as $term) {
            $query->where(function ($subQ) use ($term) {
                $subQ->where('title', 'LIKE', "%{$term}%")
                    ->orWhere('searchable_text', 'LIKE', "%{$term}%");
            });
        }

        return $query;
    }

    private function buildSnippet(string $searchableText, array $queryParameters): ?string
    {
        if ($searchableText === '' || empty($queryParameters)) {
            return null;
        }

        // Strip any residual HTML from older searchable_text data
        $searchableText = strip_tags($searchableText);
        $searchableText = html_entity_decode($searchableText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $textLower = mb_strtolower($searchableText);
        $bestPos = null;

        foreach ($queryParameters as $term) {
            $pos = mb_strpos($textLower, mb_strtolower($term));
            if ($pos !== false) {
                $bestPos = $pos;
                break;
            }
        }

        if ($bestPos === null) {
            return null;
        }

        $snippetLength = 120;
        $start = max(0, $bestPos - (int) ($snippetLength / 2));
        $end = min(mb_strlen($searchableText), $start + $snippetLength);

        // Adjust start to word boundary
        if ($start > 0) {
            $spacePos = mb_strpos($searchableText, ' ', $start);
            if ($spacePos !== false && $spacePos < $bestPos) {
                $start = $spacePos + 1;
            }
        }

        // Adjust end to word boundary
        if ($end < mb_strlen($searchableText)) {
            $spacePos = mb_strrpos(mb_substr($searchableText, 0, $end), ' ');
            if ($spacePos !== false && $spacePos > $start) {
                $end = $spacePos;
            }
        }

        $snippet = mb_substr($searchableText, $start, $end - $start);

        if ($start > 0) {
            $snippet = '...'.$snippet;
        }
        if ($end < mb_strlen($searchableText)) {
            $snippet = $snippet.'...';
        }

        return $snippet;
    }
}
