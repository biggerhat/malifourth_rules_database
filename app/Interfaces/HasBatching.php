<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface HasBatching
{
    public function batch(): BelongsTo;
}
