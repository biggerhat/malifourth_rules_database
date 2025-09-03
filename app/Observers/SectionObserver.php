<?php

namespace App\Observers;

use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Str;

class SectionObserver
{
    public function creating(Section $section): void
    {
        $leftColumn = preg_replace('/\s+/', ' ', $section->left_column ?? '');
        $rightColumn = preg_replace('/\s+/', ' ', $section->right_column ?? '');
        $section->left_column = $leftColumn;
        $section->right_column = $rightColumn;
        $section->slug = Str::slug($section->title);
        $section->searchable_text = ContentBuilder::toSearchable($leftColumn);
        $section->searchable_text .= ' ';
        $section->searchable_text .= ContentBuilder::toSearchable($rightColumn);
    }

    public function created(Section $section): void
    {
        $section->updateQuietly([
            'slug' => $section->id.'-'.Str::slug($section->title),
        ]);

    }

    public function updating(Section $section): void
    {
        $leftColumn = preg_replace('/\s+/', ' ', $section->left_column ?? '');
        $rightColumn = preg_replace('/\s+/', ' ', $section->right_column ?? '');
        $section->left_column = $leftColumn;
        $section->right_column = $rightColumn;
        $section->slug = $section->id.'-'.Str::slug($section->title);
        $section->searchable_text = ContentBuilder::toSearchable($leftColumn);
        $section->searchable_text .= ' ';
        $section->searchable_text .= ContentBuilder::toSearchable($rightColumn);
    }

    public function deleted(Section $section): void
    {
        $section->loadMissing('approval');
        $section->approval->delete();
    }
}
