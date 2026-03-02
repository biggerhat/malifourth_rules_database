<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Errata;
use App\Services\ContentBuilder\ContentBuilder;
use App\Services\ContentReferencesService;
use Illuminate\Http\Request;

class ErrataController extends Controller
{
    public function index(Request $request)
    {
        // Rules Errata: public published batches
        $rulesErrata = Batch::where('is_public', true)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (Batch $batch) => [
                'id' => $batch->id,
                'title' => $batch->title,
                'slug' => $batch->slug,
                'published_at' => $batch->published_at->format('m-d-Y'),
            ]);

        // General Errata: published standalone errata pages
        $generalErrata = Errata::whereNotNull('published_at')
            ->whereNull('newest')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (Errata $errata) => [
                'id' => $errata->id,
                'title' => $errata->title,
                'slug' => $errata->slug,
                'published_at' => $errata->published_at->format('m-d-Y'),
            ]);

        return inertia('Errata/Index', [
            'rulesErrata' => $rulesErrata,
            'generalErrata' => $generalErrata,
        ]);
    }

    public function viewBatch(Request $request, Batch $batch)
    {
        if (! $batch->is_public || ! $batch->published_at) {
            return response('', 404);
        }

        $releaseNotes = $batch->release_notes
            ? (new ContentBuilder($batch->release_notes))->getFullyHydratedContent()
            : [];

        $batch->loadMissing($batch->batchables);
        $itemChangeNotes = [];
        foreach ($batch->batchables as $relationship) {
            foreach ($batch->$relationship as $item) {
                $item->loadMissing('approval');
                if ($item->approval?->change_notes) {
                    $itemChangeNotes[] = [
                        'title' => ContentBuilder::parseTitleTags($item->title),
                        'change_notes' => (new ContentBuilder($item->approval->change_notes))->getFullyHydratedContent(),
                    ];
                }
            }
        }

        return inertia('Errata/BatchErrataView', [
            'batch' => [
                'title' => $batch->title,
                'slug' => $batch->slug,
                'published_at' => $batch->published_at->format('m-d-Y'),
                'published_by' => $batch->publishedBy?->name,
                'release_notes' => $releaseNotes,
                'item_change_notes' => $itemChangeNotes,
            ],
        ]);
    }

    public function view(Request $request, Errata $errata)
    {
        $errata->loadMissing('newestVersion', 'publishedBy');
        $errata = $errata->newestVersion ?? $errata;

        if (! $errata->published_at) {
            return response('', 404);
        }

        return inertia('Errata/ErrataView', [
            'errata' => [
                'title' => $errata->title,
                'slug' => $errata->slug,
                'content' => (new ContentBuilder($errata->content ?? ''))->getFullyHydratedContent(),
                'published_at' => $errata->published_at->format('m-d-Y'),
                'published_by' => $errata->publishedBy?->name,
            ],
            'references' => ContentReferencesService::getForModel($errata),
        ]);
    }

    public function viewHistory(Request $request, Errata $errata)
    {
        $errata->loadMissing('newestVersion', 'publishedBy');
        $currentVersion = $errata->newestVersion ?? $errata;

        if ($currentVersion->id === $errata->id) {
            return redirect()->route('errata.view', $errata->slug);
        }

        if (! $errata->published_at) {
            return response('', 404);
        }

        return inertia('Errata/ErrataView', [
            'errata' => [
                'title' => $errata->title,
                'slug' => $errata->slug,
                'content' => (new ContentBuilder($errata->content ?? ''))->getFullyHydratedContent(),
                'published_at' => $errata->published_at->format('m-d-Y'),
                'published_by' => $errata->publishedBy?->name,
            ],
            'references' => ContentReferencesService::getForModel($errata),
            'viewing_old_version' => true,
            'current_version_url' => route('errata.view', $currentVersion->slug),
        ]);
    }
}
