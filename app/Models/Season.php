<?php

namespace App\Models;

use App\Interfaces\HasBatching;
use App\Interfaces\HasPublisher;
use App\Traits\UsesVersionControl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperSeason
 */
class Season extends Model implements HasBatching, HasPublisher
{
    /** @use HasFactory<\Database\Factories\SeasonFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;
    use UsesVersionControl;

    protected $guarded = ['id'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
