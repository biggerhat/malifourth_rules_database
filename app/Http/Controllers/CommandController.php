<?php

namespace App\Http\Controllers;

use App\Models\Errata;
use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Scheme;
use App\Models\Season;
use App\Models\Section;
use App\Models\Strategy;
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

        $faqs = Faq::published()->orderBy('title', 'ASC')->get()->map(function (Faq $faq) {
            return [
                'title' => ContentBuilder::toPlainText($faq->title),
                'slug' => $faq->slug,
                'route' => route('rules.faq.view', ['faq' => $faq->slug]),
            ];
        });

        $seasons = Season::published()->orderBy('title', 'ASC')->get()->map(function (Season $season) {
            return [
                'title' => $season->title,
                'slug' => $season->slug,
                'route' => route('rules.gaining-grounds.season', ['season' => $season->slug]),
            ];
        });

        $strategies = Strategy::published()->orderBy('title', 'ASC')->get()->map(function (Strategy $strategy) {
            return [
                'title' => $strategy->title,
                'slug' => $strategy->slug,
                'route' => route('rules.gaining-grounds.strategy', ['strategy' => $strategy->slug]),
            ];
        });

        $schemes = Scheme::published()->orderBy('title', 'ASC')->get()->map(function (Scheme $scheme) {
            return [
                'title' => $scheme->title,
                'slug' => $scheme->slug,
                'route' => route('rules.gaining-grounds.scheme', ['scheme' => $scheme->slug]),
            ];
        });

        $errata = Errata::published()->orderBy('title', 'ASC')->get()->map(function (Errata $errata) {
            return [
                'title' => $errata->title,
                'slug' => $errata->slug,
                'route' => route('errata.view', ['errata' => $errata->slug]),
            ];
        });

        return response()->json([
            'pages' => $pages,
            'sections' => $sections,
            'indices' => $indices,
            'faqs' => $faqs,
            'seasons' => $seasons,
            'strategies' => $strategies,
            'schemes' => $schemes,
            'errata' => $errata,
        ]);
    }
}
