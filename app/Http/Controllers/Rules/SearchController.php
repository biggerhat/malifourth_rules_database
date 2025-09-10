<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
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
            })->get()->map(function ($page) {
                return [
                    'id' => $page->id,
                    'title' => ContentBuilder::parseTitleTags($page->title),
                    'slug' => $page->slug,
                ];
        });

        $sections = Section::query()->when($queryParameters, function ($q) use ($queryParameters) {
            foreach ($queryParameters as $parameter) {
                $q->where(function ($subQ) use ($parameter) {
                    $subQ->where('title', 'LIKE', "%{$parameter}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$parameter}%");
                });
            }
        })->get()->map(function ($section) {
            return [
                'id' => $section->id,
                'title' => ContentBuilder::parseTitleTags($section->title),
                'slug' => $section->slug,
            ];
        });

        $indices = Index::query()->when($queryParameters, function ($q) use ($queryParameters) {
            foreach ($queryParameters as $parameter) {
                $q->where(function ($subQ) use ($parameter) {
                    $subQ->where('title', 'LIKE', "%{$parameter}%")
                        ->orWhere('searchable_text', 'LIKE', "%{$parameter}%");
                });
            }
        })->get()->map(function ($index) {
            return [
                'id' => $index->id,
                'title' => ContentBuilder::parseTitleTags($index->title),
                'slug' => $index->slug,
            ];
        });

        return inertia('Search/Results', [
            'query' => $queryString,
            'pages' => $pages,
            'sections' => $sections,
            'indices' => $indices,
        ]);
    }

    private function parseQueryString(string $query): array
    {
        preg_match_all('/"([^"]+)"|\S+/', $query, $matches);
        return array_map(function ($m1, $m2) {
            return $m1 !== '' ? $m1 : $m2;
        }, $matches[1], $matches[0]);
    }
}
