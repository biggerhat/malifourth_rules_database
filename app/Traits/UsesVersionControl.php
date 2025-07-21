<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait UsesVersionControl
{
    use UsesApproval, UsesBatches;

    public const NO_APPROVAL = 'Item must be approved before it can be published.';

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous', 'id');
    }

    public function originalVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original', 'id');
    }

    public function newestVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'newest', 'id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by', 'id');
    }

    public function scopeUnpublished(Builder $query): Builder
    {
        return $query->whereNull('published_at');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * @throws \Exception
     */
    public function publish(User $publisher): self|string
    {
        $this->loadMissing('approval', 'previousVersion');

        if (! $this->approval?->approved_at) {
            throw new \Exception(self::NO_APPROVAL);
        }

        $this->update([
            'published_at' => now(),
            'published_by' => $publisher->id,
        ]);

        $previous = $this->previousVersion;
        if ($previous) {
            self::class::withTrashed()
                ->where('id', $this->original['original'])
                ->orWhere('original', $this->original['original'])
                ->whereNot('id', $this->id)
                ->update(['newest' => $this->id]);
            $previous->approval->delete();
            $previous->delete();
        }

        return $this;
    }
}
