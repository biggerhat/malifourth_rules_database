<?php

namespace App\Observers;

use App\Actions\Content\SyncContentReferencesAction;
use App\Models\Scheme;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class SchemeObserver
{
    public function creating(Scheme $scheme): void
    {
        $scheme->slug = Str::slug($scheme->title);
        $scheme->searchable_text = $this->buildSearchableText($scheme);
    }

    public function created(Scheme $scheme): void
    {
        $scheme->updateQuietly([
            'slug' => $scheme->id.'-'.Str::slug($scheme->title),
        ]);

        SyncContentReferencesAction::handle($scheme);
    }

    public function updating(Scheme $scheme): void
    {
        $scheme->slug = $scheme->id.'-'.Str::slug($scheme->title);
        $scheme->searchable_text = $this->buildSearchableText($scheme);
    }

    public function updated(Scheme $scheme): void
    {
        SyncContentReferencesAction::handle($scheme);
    }

    public function deleted(Scheme $scheme): void
    {
        $scheme->loadMissing('approval');
        $scheme->approval?->delete();
    }

    private function buildSearchableText(Scheme $scheme): string
    {
        return collect([
            $scheme->title,
            ContentBuilder::toSearchable($scheme->prerequisites ?? ''),
            ContentBuilder::toSearchable($scheme->reveal ?? ''),
            ContentBuilder::toSearchable($scheme->scoring ?? ''),
            ContentBuilder::toSearchable($scheme->additional ?? ''),
        ])->filter()->implode(' ');
    }
}
