<?php

namespace App\Observers;

use App\Models\Index;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class IndexObserver
{
    public function creating(Index $index): void
    {
        $content = preg_replace('/\s+/', ' ', $index->content ?? '');
        $index->slug = Str::slug($index->title);
        $index->content = $content;
        $index->searchable_text = ContentBuilder::toSearchable($content);
    }

    public function created(Index $index): void
    {
        $index->updateQuietly([
            'slug' => $index->id.'-'.Str::slug($index->title),
        ]);

    }

    public function updating(Index $index): void
    {
        $content = preg_replace('/\s+/', ' ', $index->content);
        $index->slug = $index->id.'-'.Str::slug($index->title);
        $index->content = $content;
        $index->searchable_text = ContentBuilder::toSearchable($content ?? '');
    }

    public function deleted(Index $index): void
    {
        $index->loadMissing('approval');
        $index->approval->delete();
    }
}
