<?php

namespace App\Traits;

use App\Models\Approval;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait UsesApproval
{
    public function approval(): MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable');
    }

    public function canBeApproved(): bool
    {
        return ! (bool) $this->loadMissing('approval')->approval?->approved_at;
    }
}
