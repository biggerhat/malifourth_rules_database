<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NavigationItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function resolvedUrl(): ?string
    {
        if (! $this->linkable) {
            return null;
        }

        return match ($this->linkable_type) {
            Page::class => route('rules.page.view', $this->linkable->slug),
            Section::class => route('rules.section.view', $this->linkable->slug),
            Index::class => route('rules.index.view', $this->linkable->slug),
            default => null,
        };
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
