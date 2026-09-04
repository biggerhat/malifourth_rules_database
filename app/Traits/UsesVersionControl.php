<?php

namespace App\Traits;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait UsesVersionControl
{
    use UsesApproval, UsesBatches;

    public const NO_APPROVAL = 'Item must be approved before it can be published.';

    /**
     * @return BelongsTo<self, $this>
     */
    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous', 'id');
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function originalVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original', 'id');
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function newestVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'newest', 'id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
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
            $previous->approval?->delete();
            $previous->delete();
        }

        return $this;
    }

    /**
     * The id that stays stable for this piece of content across every edit —
     * the first ("original") row in its version chain, or its own id if it is that row.
     */
    public function stableContentId(): int
    {
        // Inside the model class, `$this->original` resolves to Eloquent's internal
        // dirty-tracking snapshot (a protected array), not the `original` DB column —
        // that magic only kicks in for property access from outside the model class.
        // getAttribute() reads the actual column value regardless of call-site scope.
        return $this->getAttribute('original') ?? $this->id;
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return Favorite::query()
            ->where('user_id', $user->id)
            ->where('favoritable_type', static::class)
            ->where('favoritable_id', $this->stableContentId())
            ->exists();
    }

    /**
     * @return bool true if now favorited, false if the favorite was removed.
     */
    public function toggleFavorite(User $user): bool
    {
        $query = Favorite::query()
            ->where('user_id', $user->id)
            ->where('favoritable_type', static::class)
            ->where('favoritable_id', $this->stableContentId());

        if ($existing = $query->first()) {
            $existing->delete();

            return false;
        }

        Favorite::create([
            'user_id' => $user->id,
            'favoritable_type' => static::class,
            'favoritable_id' => $this->stableContentId(),
        ]);

        return true;
    }
}
