<?php

namespace App\Models;

use App\Interfaces\HasBatching;
use App\Interfaces\HasPublisher;
use App\Observers\SchemeObserver;
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
 * @mixin IdeHelperScheme
 */
#[ObservedBy(SchemeObserver::class)]
class Scheme extends Model implements HasBatching, HasPublisher
{
    use HasContentReferences;

    /** @use HasFactory<\Database\Factories\SchemeFactory> */
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

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function nextScheme1(): BelongsTo
    {
        return $this->belongsTo(Scheme::class, 'next_scheme_1');
    }

    public function nextScheme2(): BelongsTo
    {
        return $this->belongsTo(Scheme::class, 'next_scheme_2');
    }

    public function nextScheme3(): BelongsTo
    {
        return $this->belongsTo(Scheme::class, 'next_scheme_3');
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
