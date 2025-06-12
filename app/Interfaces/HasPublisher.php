<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface HasPublisher
{
    public function publishedBy(): BelongsTo;
}
