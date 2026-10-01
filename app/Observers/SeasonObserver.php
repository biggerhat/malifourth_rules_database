<?php

namespace App\Observers;

use App\Jobs\SyncContentReferencesJob;
use App\Models\Season;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class SeasonObserver
{
    public function creating(Season $season): void
    {
        $content = preg_replace('/\s+/', ' ', $season->content ?? '');
        $season->slug = Str::slug($season->title);
        $season->content = $content;
        $season->searchable_text = ContentBuilder::toSearchable($content);
    }

    public function created(Season $season): void
    {
        $season->updateQuietly([
            'slug' => $season->id.'-'.Str::slug($season->title),
        ]);

        SyncContentReferencesJob::dispatch($season);
    }

    public function updating(Season $season): void
    {
        $content = preg_replace('/\s+/', ' ', $season->content ?? '');
        $season->slug = $season->id.'-'.Str::slug($season->title);
        $season->content = $content;
        $season->searchable_text = ContentBuilder::toSearchable($content);
    }

    public function updated(Season $season): void
    {
        SyncContentReferencesJob::dispatch($season);
    }

    public function deleted(Season $season): void
    {
        $season->loadMissing('approval');
        $season->approval?->delete();
    }
}
