<?php

namespace App\Observers;

use App\Jobs\SyncContentReferencesJob;
use App\Models\Strategy;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class StrategyObserver
{
    public function creating(Strategy $strategy): void
    {
        $strategy->slug = Str::slug($strategy->title);
        $strategy->searchable_text = $this->buildSearchableText($strategy);
    }

    public function created(Strategy $strategy): void
    {
        $strategy->updateQuietly([
            'slug' => $strategy->id.'-'.Str::slug($strategy->title),
        ]);

        SyncContentReferencesJob::dispatch($strategy);
    }

    public function updating(Strategy $strategy): void
    {
        $strategy->slug = $strategy->id.'-'.Str::slug($strategy->title);
        $strategy->searchable_text = $this->buildSearchableText($strategy);
    }

    public function updated(Strategy $strategy): void
    {
        SyncContentReferencesJob::dispatch($strategy);
    }

    public function deleted(Strategy $strategy): void
    {
        $strategy->loadMissing('approval');
        $strategy->approval?->delete();
    }

    private function buildSearchableText(Strategy $strategy): string
    {
        return collect([
            $strategy->title,
            ContentBuilder::toSearchable($strategy->setup ?? ''),
            ContentBuilder::toSearchable($strategy->rules ?? ''),
            ContentBuilder::toSearchable($strategy->scoring ?? ''),
            ContentBuilder::toSearchable($strategy->additional ?? ''),
        ])->filter()->implode(' ');
    }
}
