<?php

namespace App\Models;

use App\Enums\SuitEnum;
use App\Interfaces\HasBatching;
use App\Interfaces\HasPublisher;
use App\Observers\StrategyObserver;
use App\Traits\HasContentReferences;
use App\Traits\UsesVersionControl;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperStrategy
 */
#[ObservedBy(StrategyObserver::class)]
class Strategy extends Model implements HasBatching, HasPublisher
{
    use HasContentReferences;

    /** @use HasFactory<\Database\Factories\StrategyFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;
    use UsesVersionControl;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'suit' => SuitEnum::class,
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
