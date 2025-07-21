<?php

namespace App\Models;

use App\Enums\IndexTypeEnum;
use App\Interfaces\HasBatching;
use App\Interfaces\HasPublisher;
use App\Observers\IndexObserver;
use App\Traits\UsesVersionControl;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperIndex
 */
#[ObservedBy(IndexObserver::class)]
class Index extends Model implements HasBatching, HasPublisher
{
    /** @use HasFactory<\Database\Factories\IndexFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;
    use UsesVersionControl;

    protected $guarded = ['id'];

    protected $casts = [
        'type' => IndexTypeEnum::class,
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
