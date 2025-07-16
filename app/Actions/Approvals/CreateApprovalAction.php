<?php

namespace App\Actions\Approvals;

use App\Models\Approval;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CreateApprovalAction
{
    /** @mixin Model */
    public static function handle(Model $approvable, User $initiatedBy, ?string $changeNotes = null, ?string $internalNotes = null, bool $approveDirectly = false): Approval
    {
        $approval = Approval::create([
            'approvable_type' => get_class($approvable),
            /** @phpstan-ignore-next-line property.notFound */
            'approvable_id' => $approvable->id,
            'change_notes' => $changeNotes,
            'searchable_text' => preg_replace('/{{.*?}}/', '', $changeNotes ?? ''),
            'internal_notes' => $internalNotes,
            'initiated_by' => $initiatedBy->id,
        ]);

        if ($approveDirectly) {
            $approval->update([
                'approved_at' => now(),
                'approved_by' => $initiatedBy->id,
            ]);
        }

        return $approval;
    }
}
