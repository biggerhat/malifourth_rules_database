<?php

namespace App\Observers;

use App\Models\Page;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class PageObserver
{
    public function creating(Page $page): void
    {
        $page->slug = Str::slug($page->title);
        $page->searchable_text = ContentBuilder::toSearchable($page->left_column ?? '');
        $page->searchable_text .= ' \n ';
        $page->searchable_text .= ContentBuilder::toSearchable($page->right_column ?? '');
    }

    public function created(Page $page): void
    {
        $page->updateQuietly([
            'slug' => $page->id.'-'.Str::slug($page->title),
        ]);

    }

    public function updating(Page $page): void
    {
        $page->slug = $page->id.'-'.Str::slug($page->title);
        $page->searchable_text = ContentBuilder::toSearchable($page->left_column ?? '');
        $page->searchable_text .= ' \n ';
        $page->searchable_text .= ContentBuilder::toSearchable($page->right_column ?? '');
    }
}
