<?php

namespace App\Observers;

use App\Jobs\SyncContentReferencesJob;
use App\Models\Errata;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class ErrataObserver
{
    public function creating(Errata $errata): void
    {
        $content = preg_replace('/\s+/', ' ', $errata->content ?? '');
        $errata->slug = Str::slug($errata->title);
        $errata->content = $content;
        $errata->searchable_text = $this->buildSearchableText($errata);
    }

    public function created(Errata $errata): void
    {
        $errata->updateQuietly([
            'slug' => $errata->id.'-'.Str::slug($errata->title),
        ]);

        SyncContentReferencesJob::dispatch($errata);
    }

    public function updating(Errata $errata): void
    {
        $content = preg_replace('/\s+/', ' ', $errata->content ?? '');
        $errata->slug = $errata->id.'-'.Str::slug($errata->title);
        $errata->content = $content;
        $errata->searchable_text = $this->buildSearchableText($errata);
    }

    public function updated(Errata $errata): void
    {
        SyncContentReferencesJob::dispatch($errata);
    }

    public function deleted(Errata $errata): void
    {
        $errata->loadMissing('approval');
        $errata->approval?->delete();
    }

    private function buildSearchableText(Errata $errata): string
    {
        return collect([
            $errata->title,
            ContentBuilder::toSearchable($errata->content ?? ''),
        ])->filter()->implode(' ');
    }
}
