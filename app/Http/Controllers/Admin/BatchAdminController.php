<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateApprovalAction;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\Request;

class BatchAdminController extends Controller
{
    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Batches/Index', [
            'batches' => Batch::with('approval')->orderBy('id', 'DESC')->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Batches/BatchForm');
    }

    public function edit(Request $request, Batch $batch)
    {
        return inertia('Admin/Batches/BatchForm', [
            'batch' => $batch,
        ]);
    }

    public function store(Request $request)
    {
        $batch = $this->validateAndSave($request);

        return to_route('admin.batches.index')->withMessage($batch->title.' created successfully!');
    }

    public function update(Request $request, Batch $batch)
    {
        $batch = $this->validateAndSave($request, $batch);

        return to_route('admin.batches.index')->withMessage($batch->title.' updated successfully!');
    }

    public function delete(Request $request, Batch $batch)
    {
        $name = $batch->title;

        $batch->delete();

        return to_route('admin.batches.index')->withMessage($name.' has been deleted.');
    }

    private function validateAndSave(Request $request, ?Batch $batch = null): Batch
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'release_notes' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $request->user()->id;

        if (! $batch) {
            $batch = Batch::create($validated);
            CreateApprovalAction::handle($batch, $request->user());
        } else {
            $batch->update($validated);
        }

        return $batch;
    }
}
