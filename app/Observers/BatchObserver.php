<?php

namespace App\Observers;

use App\Actions\CreateApprovalAction;
use App\Models\Batch;
use Str;

class BatchObserver
{
    public function creating(Batch $batch): void
    {
        $batch->slug = Str::slug($batch->title);
        $batch->searchable_text = preg_replace('/{{.*?}}/', '', $batch->release_notes);
    }

    public function created(Batch $batch): void
    {
        $batch->updateQuietly([
            'slug' => $batch->id.'-'.Str::slug($batch->title),
        ]);
    }

    public function updating(Batch $batch): void
    {
        $batch->slug = $batch->id.'-'.Str::slug($batch->title);
        $batch->searchable_text = preg_replace('/{{.*?}}/', '', $batch->release_notes);
    }
}
