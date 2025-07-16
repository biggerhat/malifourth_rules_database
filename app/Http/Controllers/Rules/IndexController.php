<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\Index;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function view(Request $request, Index $index)
    {
        $index->loadMissing('newestVersion', 'publishedBy');
        $index = $index->newestVersion ?? $index;

        if (!$index->published_at) {
            return response('', 404);
        }

        $content = (new ContentBuilder($index->content ?? ''))->getFullyHydratedContent();

        return inertia('Rules/IndexView', [
            'title' => $index->title,
            'type' => $index->type->value,
            'content' => $content,
            'image' => $index->image,
            'published_at' => $index->published_at->format('m-d-Y'),
            'published_by' => $index->publishedBy->name,
        ]);
    }
}
