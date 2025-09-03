<?php

namespace App\Observers;

use App\Models\Batch;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class BatchObserver
{
    public function creating(Batch $batch): void
    {
        $releaseNotes = preg_replace('/\s+/', ' ', $batch->release_notes ?? '');
        $batch->slug = Str::slug($batch->title);
        $batch->release_notes = $releaseNotes;
        //        $batch->searchable_text = preg_replace('/{{.*?}}/', '', $batch->release_notes ?? '');
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
        $releaseNotes = preg_replace('/\s+/', ' ', $batch->release_notes ?? '');
        $batch->slug = $batch->id.'-'.Str::slug($batch->title);
        $batch->release_notes = $releaseNotes;
        //        $batch->searchable_text = preg_replace('/{{.*?}}/', '', $batch->release_notes ?? '');
        $batch->searchable_text = ContentBuilder::toSearchable($releaseNotes);
    }

    public function deleted(Batch $batch): void
    {
        $batch->loadMissing('approval');
        $batch->approval->delete();
    }
}
