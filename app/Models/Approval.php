<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperApproval
 */
class Approval extends Model
{
    use LogsActivity;

    protected $table = 'approvals';

    protected $guarded = ['id'];

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by', 'id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function batches(): MorphToMany
    {
        return $this->morphedByMany(Batch::class, 'approveable', 'approvals');
    }

    public function pages(): MorphToMany
    {
        return $this->morphedByMany(Page::class, 'approveable', 'approvals');
    }

    public function indices(): MorphToMany
    {
        return $this->morphedByMany(Index::class, 'approveable', 'approvals');
    }

    public function schemes(): MorphToMany
    {
        return $this->morphedByMany(Scheme::class, 'approveable', 'approvals');
    }

    public function seasons(): MorphToMany
    {
        return $this->morphedByMany(Season::class, 'approveable', 'approvals');
    }

    public function sections(): MorphToMany
    {
        return $this->morphedByMany(Section::class, 'approveable', 'approvals');
    }

    public function strategies(): MorphToMany
    {
        return $this->morphedByMany(Strategy::class, 'approveable', 'approvals');
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
