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
        $answer = preg_replace('/\s+/', ' ', $faq->answer ?? '');
        $title = preg_replace('/\s+/', ' ', $faq->title ?? '');
        $faq->slug = Str::slug($faq->category?->value ?? 'faq');
        $faq->title = $title;
        $faq->answer = $answer;
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
        $answer = preg_replace('/\s+/', ' ', $faq->answer);
        $title = preg_replace('/\s+/', ' ', $faq->title ?? '');
        $faq->slug = $faq->id.'-'.Str::slug($faq->category?->value ?? 'faq');
        $faq->title = $title;
        $faq->answer = $answer;
        $faq->searchable_text = ContentBuilder::toPlainText($title).' '.ContentBuilder::toPlainText($answer ?? '');
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
