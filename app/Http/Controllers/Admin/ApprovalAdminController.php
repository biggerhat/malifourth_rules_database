<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApprovalResource;
use App\Models\Approval;
use Illuminate\Http\Request;

class ApprovalAdminController extends Controller
{
    public function index(Request $request)
    {
        return inertia('Admin/Approvals/Index', [
            'approvals' => ApprovalResource::collection(Approval::with('approvable')->unapproved()->orderBy('created_at', 'ASC')->get())->toArray($request),
        ]);
    }

    public function edit(Request $request, Approval $approval) {}

    public function update(Request $request, Approval $approval)
    {
        $validated = $request->validate([
            'approved' => ['required', 'boolean'],
            'internal_notes' => ['nullable', 'string'],
        ]);
        $title = $approval->loadMissing('approvable')->approvable->title ?? '';

        if ($validated['approved']) {
            $approval->update([
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
                'internal_notes' => $validated['internal_notes'] ?? null,
            ]);

            return redirect()->back()->withMessage($title.' has been approved and is ready to be published!');
        }

        return to_route('admin.approvals.index');
    }
}
