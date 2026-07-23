<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperCardErrataEntry
 */
class CardErrataEntry extends Model
{
    /** @use HasFactory<\Database\Factories\CardErrataEntryFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return BelongsTo<CardErrata, $this>
     */
    public function cardErrata(): BelongsTo
    {
        return $this->belongsTo(CardErrata::class);
    }
}
