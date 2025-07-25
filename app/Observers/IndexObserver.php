<?php

namespace App\Observers;

use App\Models\Index;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class IndexObserver
{
    public function creating(Index $index): void
    {
        $index->slug = Str::slug($index->title);
        $index->searchable_text = ContentBuilder::toSearchable($index->content ?? '');
    }

    public function created(Index $index): void
    {
        $index->updateQuietly([
            'slug' => $index->id.'-'.Str::slug($index->title),
        ]);

    }

    public function updating(Index $index): void
    {
        $index->slug = $index->id.'-'.Str::slug($index->title);
        $index->searchable_text = ContentBuilder::toSearchable($index->content ?? '');
    }
}
