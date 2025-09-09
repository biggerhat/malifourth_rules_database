<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function view(Request $request, Section $section)
    {
        $section->loadMissing('newestVersion', 'publishedBy');
        $section = $section->newestVersion ?? $section;

        if (! $section->published_at) {
            return response('', 404);
        }

        $leftColumn = (new ContentBuilder($section->left_column ?? ''))->getFullyHydratedContent();
        $rightColumn = (new ContentBuilder($section->right_column ?? ''))->getFullyHydratedContent();

        return inertia('Rules/SectionView', [
            'title' => ContentBuilder::parseTitleTags($section->title),
            'left_column' => $leftColumn,
            'right_column' => $rightColumn,
            'published_at' => $section->published_at->format('m-d-Y'),
            'published_by' => $section->publishedBy->name,
        ]);
    }
}
