<?php

namespace App\Traits;

use App\Models\Approval;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait UsesApproval
{
    public function approval(): MorphToMany
    {
        return $this->morphToMany(Approval::class, 'approvable');
    }
}
