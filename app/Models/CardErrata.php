<?php

namespace App\Models;

use App\Interfaces\HasBatching;
use App\Interfaces\HasPublisher;
use App\Observers\CardErrataObserver;
use App\Traits\UsesVersionControl;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperCardErrata
 */
#[ObservedBy(CardErrataObserver::class)]
class CardErrata extends Model implements HasBatching, HasPublisher
{
    /** @use HasFactory<\Database\Factories\CardErrataFactory> */
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

    /**
     * Generic admin/approval UIs (ApprovalResource, BatchableListResource) fall back to
     * `title` for every approvable content type; CardErrata has no `title` column so we
     * synthesize one here instead of special-casing those shared resources.
     */
    public function getTitleAttribute(): string
    {
        return $this->card_name;
    }

    /**
     * @return HasMany<CardErrataEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(CardErrataEntry::class)->orderBy('sort_order');
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
