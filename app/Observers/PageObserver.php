<?php

namespace App\Observers;

use App\Models\Page;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class PageObserver
{
    public function creating(Page $page): void
    {
        $content = preg_replace('/\s+/', ' ', $page->content ?? '');
        $page->slug = Str::slug($page->title);
        $page->content = $content;
        $page->searchable_text = ContentBuilder::toSearchable($content);
    }

    public function created(Page $page): void
    {
        $page->updateQuietly([
            'slug' => $page->id.'-'.Str::slug($page->title),
        ]);

    }

    public function updating(Page $page): void
    {
        $content = preg_replace('/\s+/', ' ', $page->content ?? '');
        $page->slug = $page->id.'-'.Str::slug($page->title);
        $page->content = $content;
        $page->searchable_text = ContentBuilder::toSearchable($content);
    }

    public function deleted(Page $page): void
    {
        $page->loadMissing('approval');
        $page->approval->delete();
    }
}
