<?php

namespace App\Observers;

use App\Models\Batch;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class BatchObserver
{
    public function creating(Batch $batch): void
    {
        $releaseNotes = $batch->release_notes ?? '';
        if (! ContentBuilder::detectTipTapJson($releaseNotes)) {
            $releaseNotes = preg_replace('/\s+/', ' ', $releaseNotes);
            $batch->release_notes = $releaseNotes;
        }
        $batch->slug = Str::slug($batch->title);
        $batch->searchable_text = ContentBuilder::toSearchable($releaseNotes);
    }

    public function created(Batch $batch): void
    {
        $batch->updateQuietly([
            'slug' => $batch->id.'-'.Str::slug($batch->title),
        ]);
    }

    public function updating(Batch $batch): void
    {
        $releaseNotes = $batch->release_notes ?? '';
        if (! ContentBuilder::detectTipTapJson($releaseNotes)) {
            $releaseNotes = preg_replace('/\s+/', ' ', $releaseNotes);
            $batch->release_notes = $releaseNotes;
        }
        $batch->slug = $batch->id.'-'.Str::slug($batch->title);
        $batch->searchable_text = ContentBuilder::toSearchable($releaseNotes);
    }

    public function deleted(Batch $batch): void
    {
        $batch->loadMissing('approval');
        $batch->approval?->delete();
    }
}
