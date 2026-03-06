<?php

namespace App\Observers;

use App\Actions\Content\SyncContentReferencesAction;
use App\Models\SeasonPage;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class SeasonPageObserver
{
    public function creating(SeasonPage $seasonPage): void
    {
        $content = $seasonPage->content ?? '';
        if (! ContentBuilder::detectTipTapJson($content)) {
            $content = preg_replace('/\s+/', ' ', $content);
            $seasonPage->content = $content;
        }
        $seasonPage->slug = Str::slug($seasonPage->title);
        $seasonPage->searchable_text = $this->buildSearchableText($seasonPage);
    }

    public function created(SeasonPage $seasonPage): void
    {
        $seasonPage->updateQuietly([
            'slug' => $seasonPage->id.'-'.Str::slug($seasonPage->title),
        ]);

        SyncContentReferencesAction::handle($seasonPage);
    }

    public function updating(SeasonPage $seasonPage): void
    {
        $content = $seasonPage->content ?? '';
        if (! ContentBuilder::detectTipTapJson($content)) {
            $content = preg_replace('/\s+/', ' ', $content);
            $seasonPage->content = $content;
        }
        $seasonPage->slug = $seasonPage->id.'-'.Str::slug($seasonPage->title);
        $seasonPage->searchable_text = $this->buildSearchableText($seasonPage);
    }

    public function updated(SeasonPage $seasonPage): void
    {
        SyncContentReferencesAction::handle($seasonPage);
    }

    public function deleted(SeasonPage $seasonPage): void
    {
        $seasonPage->loadMissing('approval');
        $seasonPage->approval?->delete();
    }

    private function buildSearchableText(SeasonPage $seasonPage): string
    {
        return collect([
            $seasonPage->title,
            ContentBuilder::toSearchable($seasonPage->content ?? ''),
        ])->filter()->implode(' ');
    }
}
