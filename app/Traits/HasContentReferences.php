<?php

namespace App\Traits;

use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasContentReferences
{
    public function referencedFaqs(): MorphToMany
    {
        return $this->morphToMany(Faq::class, 'faqable');
    }

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

    public function referencedByFaqs(): MorphToMany
    {
        return $this->morphedByMany(Faq::class, $this->getReferenceMorphName());
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
            Faq::class => 'faqable',
            Index::class => 'indexable',
            Section::class => 'sectionable',
            Page::class => 'pageable',
        };
    }
}
