<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\Index;
use App\Services\ContentBuilder\ContentBuilder;
use App\Services\ContentReferencesService;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function view(Request $request, Index $index)
    {
        $index->loadMissing('newestVersion', 'publishedBy');
        $index = $index->newestVersion ?? $index;

        if (! $index->published_at) {
            return response('', 404);
        }

        $content = (new ContentBuilder($index->content ?? ''))->getFullyHydratedContent();

        return inertia('Rules/IndexView', [
            'title' => ContentBuilder::parseTitleTags($index->title),
            'title_text' => ContentBuilder::toPlainText($index->title),
            'type' => $index->type->value,
            'content' => $content,
            'image' => $index->image,
            'published_at' => $index->published_at->format('m-d-Y'),
            'published_by' => $index->publishedBy->name,
            'references' => ContentReferencesService::getForModel($index),
        ]);
    }

    public function viewHistory(Request $request, Index $index)
    {
        $index->loadMissing('newestVersion', 'publishedBy');

        $currentVersion = $index->newestVersion ?? $index;

        if ($currentVersion->id === $index->id) {
            return redirect()->route('rules.index.view', $index->slug);
        }

        if (! $index->published_at) {
            return response('', 404);
        }

        $content = (new ContentBuilder($index->content ?? ''))->getFullyHydratedContent();

        return inertia('Rules/IndexView', [
            'title' => ContentBuilder::parseTitleTags($index->title),
            'title_text' => ContentBuilder::toPlainText($index->title),
            'type' => $index->type->value,
            'content' => $content,
            'image' => $index->image,
            'published_at' => $index->published_at->format('m-d-Y'),
            'published_by' => $index->publishedBy->name,
            'references' => ContentReferencesService::getForModel($index),
            'viewing_old_version' => true,
            'current_version_url' => route('rules.index.view', $currentVersion->slug),
        ]);
    }
}
