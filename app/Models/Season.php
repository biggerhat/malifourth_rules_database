<?php

namespace App\Models;

use App\Interfaces\HasBatching;
use App\Interfaces\HasPublisher;
use App\Observers\SeasonObserver;
use App\Traits\HasContentReferences;
use App\Traits\UsesVersionControl;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperSeason
 */
#[ObservedBy(SeasonObserver::class)]
class Season extends Model implements HasBatching, HasPublisher
{
    use HasContentReferences;

    /** @use HasFactory<\Database\Factories\SeasonFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;
    use UsesVersionControl;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function strategies(): HasMany
    {
        return $this->hasMany(Strategy::class);
    }

    public function schemes(): HasMany
    {
        return $this->hasMany(Scheme::class);
    }

    public function seasonPages(): HasMany
    {
        return $this->hasMany(SeasonPage::class);
    }

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
            // Migrate child relationships to the new season version
            SeasonPage::where('season_id', $previous->id)->update(['season_id' => $this->id]);
            Strategy::where('season_id', $previous->id)->update(['season_id' => $this->id]);
            Scheme::where('season_id', $previous->id)->update(['season_id' => $this->id]);

            self::withTrashed()
                ->where('id', $this->original['original'])
                ->orWhere('original', $this->original['original'])
                ->whereNot('id', $this->id)
                ->update(['newest' => $this->id]);
            $previous->approval?->delete();
            $previous->delete();
        }

        return $this;
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
