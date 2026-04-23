<?php

namespace App\Observers;

use App\Actions\Content\SyncContentReferencesAction;
use App\Models\Index;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class IndexObserver
{
    public function creating(Index $index): void
    {
        $content = $index->content ?? '';
        if (! ContentBuilder::detectTipTapJson($content)) {
            $content = preg_replace('/\s+/', ' ', $content);
            $index->content = $content;
        }
        $index->slug = Str::slug($index->title);
        $index->searchable_text = ContentBuilder::toSearchable($content);
    }

    public function created(Index $index): void
    {
        $index->updateQuietly([
            'slug' => $index->id.'-'.Str::slug($index->title),
        ]);

        SyncContentReferencesAction::handle($index);
    }

    public function updated(Index $index): void
    {
        SyncContentReferencesAction::handle($index);
    }

    public function updating(Index $index): void
    {
        $content = $index->content ?? '';
        if (! ContentBuilder::detectTipTapJson($content)) {
            $content = preg_replace('/\s+/', ' ', $content);
            $index->content = $content;
        }
        $index->slug = $index->id.'-'.Str::slug($index->title);
        $index->searchable_text = ContentBuilder::toSearchable($content);
    }

    public function deleted(Index $index): void
    {
        $index->loadMissing('approval');
        $index->approval?->delete();
    }
}
