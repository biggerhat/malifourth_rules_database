<?php

namespace App\Models;

use App\Interfaces\HasPublisher;
use App\Observers\BatchObserver;
use App\Traits\UsesApproval;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperBatch
 */
#[ObservedBy(BatchObserver::class)]
class Batch extends Model implements HasPublisher
{
    /** @use HasFactory<\Database\Factories\BatchFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;
    use UsesApproval;

    protected $guarded = ['id'];

    public array $batchables = [
        'indices',
        'pages',
        'sections',
        'seasons',
        'schemes',
        'strategies',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by', 'id');
    }

    public function scopeUnpublished(Builder $query): Builder
    {
        return $query->whereNull('published_at');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function indices(): HasMany
    {
        return $this->hasMany(Index::class, 'batch_id', 'id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class, 'batch_id', 'id');
    }

    public function schemes(): HasMany
    {
        return $this->hasMany(Scheme::class, 'batch_id', 'id');
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class, 'batch_id', 'id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'batch_id', 'id');
    }

    public function strategies(): HasMany
    {
        return $this->hasMany(Strategy::class, 'batch_id', 'id');
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
