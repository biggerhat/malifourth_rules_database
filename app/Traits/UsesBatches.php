<?php

namespace App\Traits;

use App\Models\Batch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait UsesBatches
{
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id', 'id');
    }
}
