<?php

namespace App\Observers;

use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class SectionObserver
{
    public function creating(Section $section): void
    {
        $section->slug = Str::slug($section->title);
        $section->searchable_text = ContentBuilder::toSearchable($section->content ?? '');
    }

    public function created(Section $section): void
    {
        $section->updateQuietly([
            'slug' => $section->id.'-'.Str::slug($section->title),
        ]);

    }

    public function updating(Section $section): void
    {
        $section->slug = $section->id.'-'.Str::slug($section->title);
        $section->searchable_text = ContentBuilder::toSearchable($section->content ?? '');
    }
}
