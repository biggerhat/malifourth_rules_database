<?php

namespace App\Traits;

use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasContentReferences
{
    public function referencedIndices(): MorphToMany
    {
        return $this->morphToMany(Index::class, 'indexable');
    }

    public function referencedSections(): MorphToMany
    {
        return $this->morphToMany(Section::class, 'sectionable');
    }

    public function referencedPages(): MorphToMany
    {
        return $this->morphToMany(Page::class, 'pageable');
    }

    public function referencedByIndices(): MorphToMany
    {
        return $this->morphedByMany(Index::class, $this->getReferenceMorphName());
    }

    public function referencedBySections(): MorphToMany
    {
        return $this->morphedByMany(Section::class, $this->getReferenceMorphName());
    }

    public function referencedByPages(): MorphToMany
    {
        return $this->morphedByMany(Page::class, $this->getReferenceMorphName());
    }

    private function getReferenceMorphName(): string
    {
        return match (static::class) {
            Index::class => 'indexable',
            Section::class => 'sectionable',
            Page::class => 'pageable',
        };
    }
}
