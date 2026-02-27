<?php

namespace App\Models;

use App\Enums\FaqCategoryEnum;
use App\Interfaces\HasBatching;
use App\Interfaces\HasPublisher;
use App\Observers\FaqObserver;
use App\Traits\HasContentReferences;
use App\Traits\UsesVersionControl;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperFaq
 */
#[ObservedBy(FaqObserver::class)]
class Faq extends Model implements HasBatching, HasPublisher
{
    /** @use HasFactory<\Database\Factories\FaqFactory> */
    use HasContentReferences;

    use HasFactory;
    use LogsActivity;
    use SoftDeletes;
    use UsesVersionControl;

    protected $guarded = ['id'];

    protected $casts = [
        'category' => FaqCategoryEnum::class,
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
