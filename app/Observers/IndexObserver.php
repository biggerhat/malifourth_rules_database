<?php

namespace App\Observers;

use App\Models\Index;
use Str;

class IndexObserver
{
    public function creating(Index $index): void
    {
        $index->slug = Str::slug($index->title);
        $index->created_by = Auth()->user();
    }

    public function updating(Index $index): void
    {
        $index->slug = Str::slug($index->title);
    }
}
