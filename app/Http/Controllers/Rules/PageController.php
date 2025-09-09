<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $page = Page::with('newestVersion', 'publishedBy')->published()->orderBy('page_number', 'ASC')->first();
        $page = $page->newestVersion ?? $page;

        /** @phpstan-ignore-next-line nullsafe.neverNull */
        if (! $page || ! $page?->published_at) {
            return response('', 404);
        }

        $content = (new ContentBuilder($page->content ?? ''))->getFullyHydratedContent();

        return inertia('Rules/PageView', [
            'pages' => Page::orderBy('page_number', 'ASC')->published()->get()->map(function (Page $page) {
                return [
                    'slug' => $page->slug,
                    'title' => ContentBuilder::parseTitleTags($page->title),
                    'page_number' => $page->page_number,
                ];
            }),
            'title' => ContentBuilder::parseTitleTags($page->title),
            'slug' => $page->slug,
            'content' => $content,
            'page_number' => $page->page_number,
            'book_page_numbers' => $page->book_page_numbers,
            'published_at' => $page->published_at->format('m-d-Y'),
            'published_by' => $page->publishedBy->name,
            'previous_page' => $page->previousPage()?->slug,
            'next_page' => $page->nextPage()?->slug,
        ]);
    }

    public function view(Request $request, Page $page)
    {
        $page->loadMissing('newestVersion', 'publishedBy');
        $page = $page->newestVersion ?? $page;

        if (! $page->published_at) {
            return response('', 404);
        }

        $content = (new ContentBuilder($page->content ?? ''))->getFullyHydratedContent();

        return inertia('Rules/PageView', [
            'pages' => Page::orderBy('page_number', 'ASC')->published()->get()->map(function (Page $page) {
                return [
                    'slug' => $page->slug,
                    'title' => ContentBuilder::parseTitleTags($page->title),
                    'page_number' => $page->page_number,
                ];
            }),
            'title' => ContentBuilder::parseTitleTags($page->title),
            'slug' => $page->slug,
            'content' => $content,
            'page_number' => $page->page_number,
            'book_page_numbers' => $page->book_page_numbers,
            'published_at' => $page->published_at->format('m-d-Y'),
            'published_by' => $page->publishedBy->name,
            'previous_page' => $page->previousPage()?->slug,
            'next_page' => $page->nextPage()?->slug,
        ]);
    }
}
