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
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function view(Request $request)
    {
        $queryString = $request->input('q');
        $queryParameters = $this->parseQueryString($queryString);

        $pages = Page::query()->when($queryParameters, function ($q) use ($queryParameters) {
            foreach ($queryParameters as $parameter) {
                $q->where(function ($subQ) use ($parameter) {
                    $subQ->where('title', 'LIKE', "%{$parameter}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$parameter}%");
                });
            }
        })->get()->map(function ($page) use ($queryParameters) {
            return [
                'id' => $page->id,
                'title' => ContentBuilder::parseTitleTags($page->title),
                'slug' => $page->slug,
                'snippet' => $this->buildSnippet($page->searchable_text ?? '', $queryParameters),
            ];
        });

        $sections = Section::query()->when($queryParameters, function ($q) use ($queryParameters) {
            foreach ($queryParameters as $parameter) {
                $q->where(function ($subQ) use ($parameter) {
                    $subQ->where('title', 'LIKE', "%{$parameter}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$parameter}%");
                });
            }
        })->get()->map(function ($section) use ($queryParameters) {
            return [
                'id' => $section->id,
                'title' => ContentBuilder::parseTitleTags($section->title),
                'slug' => $section->slug,
                'snippet' => $this->buildSnippet($section->searchable_text ?? '', $queryParameters),
            ];
        });

        $indices = Index::query()->when($queryParameters, function ($q) use ($queryParameters) {
            foreach ($queryParameters as $parameter) {
                $q->where(function ($subQ) use ($parameter) {
                    $subQ->where('title', 'LIKE', "%{$parameter}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$parameter}%");
                });
            }
        })->get()->map(function ($index) use ($queryParameters) {
            return [
                'id' => $index->id,
                'title' => ContentBuilder::parseTitleTags($index->title),
                'slug' => $index->slug,
                'snippet' => $this->buildSnippet($index->searchable_text ?? '', $queryParameters),
            ];
        });

        $faqs = Faq::query()->when($queryParameters, function ($q) use ($queryParameters) {
            foreach ($queryParameters as $parameter) {
                $q->where(function ($subQ) use ($parameter) {
                    $subQ->where('title', 'LIKE', "%{$parameter}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$parameter}%");
                });
            }
        })->get()->map(function ($faq) use ($queryParameters) {
            return [
                'id' => $faq->id,
                'title' => ContentBuilder::toPlainText($faq->title),
                'slug' => $faq->slug,
                'snippet' => $this->buildSnippet($faq->searchable_text ?? '', $queryParameters),
            ];
        });

        $seasons = Season::query()->when($queryParameters, function ($q) use ($queryParameters) {
            foreach ($queryParameters as $parameter) {
                $q->where(function ($subQ) use ($parameter) {
                    $subQ->where('title', 'LIKE', "%{$parameter}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$parameter}%");
                });
            }
        })->get()->map(function ($season) use ($queryParameters) {
            return [
                'id' => $season->id,
                'title' => $season->title,
                'slug' => $season->slug,
                'snippet' => $this->buildSnippet($season->searchable_text ?? '', $queryParameters),
            ];
        });

        $strategies = Strategy::query()->when($queryParameters, function ($q) use ($queryParameters) {
            foreach ($queryParameters as $parameter) {
                $q->where(function ($subQ) use ($parameter) {
                    $subQ->where('title', 'LIKE', "%{$parameter}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$parameter}%");
                });
            }
        })->get()->map(function ($strategy) use ($queryParameters) {
            return [
                'id' => $strategy->id,
                'title' => $strategy->title,
                'slug' => $strategy->slug,
                'snippet' => $this->buildSnippet($strategy->searchable_text ?? '', $queryParameters),
            ];
        });

        $seasonPages = SeasonPage::query()->when($queryParameters, function ($q) use ($queryParameters) {
            foreach ($queryParameters as $parameter) {
                $q->where(function ($subQ) use ($parameter) {
                    $subQ->where('title', 'LIKE', "%{$parameter}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$parameter}%");
                });
            }
        })->with('season')->get()->map(function ($sp) use ($queryParameters) {
            return [
                'id' => $sp->id,
                'title' => $sp->title,
                'slug' => $sp->slug,
                'season_slug' => $sp->season?->slug,
                'snippet' => $this->buildSnippet($sp->searchable_text ?? '', $queryParameters),
            ];
        });

        $errata = Errata::query()->when($queryParameters, function ($q) use ($queryParameters) {
            foreach ($queryParameters as $parameter) {
                $q->where(function ($subQ) use ($parameter) {
                    $subQ->where('title', 'LIKE', "%{$parameter}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$parameter}%");
                });
            }
        })->get()->map(function ($errata) use ($queryParameters) {
            return [
                'id' => $errata->id,
                'title' => $errata->title,
                'slug' => $errata->slug,
                'snippet' => $this->buildSnippet($errata->searchable_text ?? '', $queryParameters),
            ];
        });

        $schemes = Scheme::query()->when($queryParameters, function ($q) use ($queryParameters) {
            foreach ($queryParameters as $parameter) {
                $q->where(function ($subQ) use ($parameter) {
                    $subQ->where('title', 'LIKE', "%{$parameter}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$parameter}%");
                });
            }
        })->get()->map(function ($scheme) use ($queryParameters) {
            return [
                'id' => $scheme->id,
                'title' => $scheme->title,
                'slug' => $scheme->slug,
                'snippet' => $this->buildSnippet($scheme->searchable_text ?? '', $queryParameters),
            ];
        });

        return inertia('Search/Results', [
            'query' => $queryString,
            'queryTerms' => $queryParameters,
            'pages' => $pages,
            'sections' => $sections,
            'indices' => $indices,
            'faqs' => $faqs,
            'seasons' => $seasons,
            'strategies' => $strategies,
            'season_pages' => $seasonPages,
            'schemes' => $schemes,
            'errata' => $errata,
        ]);
    }

    private function parseQueryString(string $query): array
    {
        preg_match_all('/"([^"]+)"|\S+/', $query, $matches);

        return array_map(function ($m1, $m2) {
            return $m1 !== '' ? $m1 : $m2;
        }, $matches[1], $matches[0]);
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
