<?php

namespace App\Models;

use App\Interfaces\HasPublisher;
use App\Traits\UsesApproval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperBatch
 */
class Batch extends Model implements HasPublisher
{
    /** @use HasFactory<\Database\Factories\BatchFactory> */
    use HasFactory;

    use LogsActivity;
    use UsesApproval;

    protected $guarded = ['id'];

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by', 'id');
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
