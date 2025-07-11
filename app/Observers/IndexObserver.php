<?php

namespace App\Observers;

use App\Models\Index;
use Str;

class IndexObserver
{
    public function creating(Index $index): void
    {
        $index->slug = Str::slug($index->title);
        $index->searchable_text = preg_replace('/{{.*?}}/', '', $index->content);
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
        $index->searchable_text = preg_replace('/{{.*?}}/', '', $index->content);
    }
}
