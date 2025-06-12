<?php

namespace App\Models;

use App\Interfaces\HasBatching;
use App\Interfaces\HasPublisher;
use App\Traits\UsesVersionControl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperScheme
 */
class Scheme extends Model implements HasBatching, HasPublisher
{
    /** @use HasFactory<\Database\Factories\SchemeFactory> */
    use HasFactory;

    use LogsActivity;
    use UsesVersionControl;

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
