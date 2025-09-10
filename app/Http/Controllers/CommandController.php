<?php

namespace App\Http\Controllers;

use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $pages = Page::published()->orderBy('title', 'ASC')->get()->map(function (Page $page) {
            return [
                'title' => ContentBuilder::parseTitleTags($page->title),
                'slug' => $page->slug,
                'route' => route('rules.page.view', ['page' => $page->slug]),
            ];
        });

        $sections = Section::published()->orderBy('title', 'ASC')->get()->map(function (Section $section) {
            return [
                'title' => ContentBuilder::parseTitleTags($section->title),
                'slug' => $section->slug,
                'route' => route('rules.section.view', ['section' => $section->slug]),
            ];
        });

        $indices = Index::published()->orderBy('title', 'ASC')->get()->map(function (Index $index) {
            return [
                'title' => ContentBuilder::parseTitleTags($index->title),
                'slug' => $index->slug,
                'route' => route('rules.index.view', ['index' => $index->slug]),
            ];
        });

        return response()->json([
            'pages' => $pages,
            'sections' => $sections,
            'indices' => $indices,
        ]);
    }
}
