<?php

namespace App\Actions;

use App\Models\Approval;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CreateApprovalAction
{
    /** @mixin Model */
    public static function handle(Model $approvable, User $initiatedBy, ?string $changeNotes = null, ?string $internalNotes = null): Approval
    {
        return Approval::create([
            'approvable_type' => get_class($approvable),
            'approvable_id' => $approvable->id,
            'change_notes' => $changeNotes,
            'searchable_text' => preg_replace('/{{.*?}}/', '', $changeNotes ?? ''),
            'internal_notes' => $internalNotes,
            'initiated_by' => $initiatedBy->id,
        ]);
    }
}
