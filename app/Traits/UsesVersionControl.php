<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait UsesVersionControl
{
    use UsesApproval, UsesBatches;

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous', 'id');
    }

    public function originalVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original', 'id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by', 'id');
    }

    public function scopeUnpublished(Builder $query): Builder
    {
        return $query->whereNull('published_at');
    }
}
