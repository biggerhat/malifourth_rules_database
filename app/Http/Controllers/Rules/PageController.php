<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request) {
        $page = Page::with('newestVersion', 'publishedBy')->orderBy('page_number', 'ASC')->first();
        $page = $page->newestVersion ?? $page;

        if (!$page->published_at) {
            return response('', 404);
        }

        $leftColumn = (new ContentBuilder($page->left_column ?? ''))->getFullyHydratedContent();
        $rightColumn = (new ContentBuilder($page->right_column ?? ''))->getFullyHydratedContent();

        return inertia('Rules/PageView', [
            'pages' => Page::orderBy('page_number', 'ASC')->get()->map(function (Page $page) {
                return [
                    'slug' => $page->slug,
                    'title' => $page->title,
                    'page_number' => $page->page_number,
                ];
            }),
            'title' => $page->title,
            'slug' => $page->slug,
            'left_column' => $leftColumn,
            'right_column' => $rightColumn,
            'page_number' => $page->page_number,
            'book_page_numbers' => $page->book_page_numbers,
            'published_at' => $page->published_at->format('m-d-Y'),
            'published_by' => $page->publishedBy->name,
        ]);
    }

    public function view(Request $request, Page $page)
    {
        $page->loadMissing('newestVersion', 'publishedBy');
        $page = $page->newestVersion ?? $page;

        if (!$page->published_at) {
            return response('', 404);
        }

        $leftColumn = (new ContentBuilder($page->left_column ?? ''))->getFullyHydratedContent();
        $rightColumn = (new ContentBuilder($page->right_column ?? ''))->getFullyHydratedContent();

        return inertia('Rules/PageView', [
            'pages' => Page::orderBy('page_number', 'ASC')->get()->map(function (Page $page) {
                return [
                    'slug' => $page->slug,
                    'title' => $page->title,
                    'page_number' => $page->page_number,
                ];
            }),
            'title' => $page->title,
            'slug' => $page->slug,
            'left_column' => $leftColumn,
            'right_column' => $rightColumn,
            'page_number' => $page->page_number,
            'book_page_numbers' => $page->book_page_numbers,
            'published_at' => $page->published_at->format('m-d-Y'),
            'published_by' => $page->publishedBy->name,
        ]);
    }
}
