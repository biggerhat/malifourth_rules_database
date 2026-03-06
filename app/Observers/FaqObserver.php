<?php

namespace App\Observers;

use App\Actions\Content\SyncContentReferencesAction;
use App\Models\Faq;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class FaqObserver
{
    public function creating(Faq $faq): void
    {
        $answer = $faq->answer ?? '';
        $title = $faq->title ?? '';
        if (! ContentBuilder::detectTipTapJson($answer)) {
            $answer = preg_replace('/\s+/', ' ', $answer);
            $faq->answer = $answer;
        }
        if (! ContentBuilder::detectTipTapJson($title)) {
            $title = preg_replace('/\s+/', ' ', $title);
            $faq->title = $title;
        }
        $faq->slug = Str::slug($faq->category?->value ?? 'faq');
        $faq->searchable_text = ContentBuilder::toPlainText($title).' '.ContentBuilder::toPlainText($answer);
    }

    public function created(Faq $faq): void
    {
        $faq->updateQuietly([
            'slug' => $faq->id.'-'.Str::slug($faq->category?->value ?? 'faq'),
        ]);

        SyncContentReferencesAction::handle($faq);
    }

    public function updating(Faq $faq): void
    {
        $answer = $faq->answer ?? '';
        $title = $faq->title ?? '';
        if (! ContentBuilder::detectTipTapJson($answer)) {
            $answer = preg_replace('/\s+/', ' ', $answer);
            $faq->answer = $answer;
        }
        if (! ContentBuilder::detectTipTapJson($title)) {
            $title = preg_replace('/\s+/', ' ', $title);
            $faq->title = $title;
        }
        $faq->slug = $faq->id.'-'.Str::slug($faq->category?->value ?? 'faq');
        $faq->searchable_text = ContentBuilder::toPlainText($title).' '.ContentBuilder::toPlainText($answer);
    }

    public function updated(Faq $faq): void
    {
        SyncContentReferencesAction::handle($faq);
    }

    public function deleted(Faq $faq): void
    {
        $faq->loadMissing('approval');
        $faq->approval?->delete();
    }
}
