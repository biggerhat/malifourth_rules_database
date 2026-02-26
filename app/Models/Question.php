<?php

namespace App\Models;

use App\Enums\QuestionSectionEnum;
use App\Interfaces\HasBatching;
use App\Interfaces\HasPublisher;
use App\Traits\UsesVersionControl;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Question extends Model implements HasBatching, HasPublisher
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;
    use UsesVersionControl;

    protected $guarded = ['id'];

    protected $casts = [
        'section' => QuestionSectionEnum::class,
        'published_at' => 'datetime',
    ];

    public function scopeSection(Builder $query, QuestionSectionEnum $section): Builder
    {
        return $query->where('section', $section->value);
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
