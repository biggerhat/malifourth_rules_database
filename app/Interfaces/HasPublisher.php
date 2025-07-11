<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface HasPublisher
{
    public function publishedBy(): BelongsTo;

    public function scopeUnpublished(Builder $query): Builder;
}
