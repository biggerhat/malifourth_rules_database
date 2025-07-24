<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\MessageTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\BatchableListResource;
use App\Http\Resources\BatchListResource;
use App\Http\Resources\IndexListResource;
use App\Http\Resources\PageListResource;
use App\Http\Resources\SectionListResource;
use App\Models\Batch;
use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use Illuminate\Http\Request;

class BatchAdminController extends Controller
{
    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Batches/Index', [
            'batches' => BatchListResource::collection(Batch::with('approval')->orderBy('id', 'DESC')->get())->toArray($request),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Batches/BatchForm', [
            'indices' => IndexListResource::collection(
                Index::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'sections' => SectionListResource::collection(
                Section::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'pages' => PageListResource::collection(
                Page::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
        ]);
    }

    public function edit(Request $request, Batch $batch)
    {
        return inertia('Admin/Batches/BatchForm', [
            'batch' => $batch,
            'batchables' => BatchableListResource::collection($batch->flattenBatchables())->toArray($request),
            'indices' => IndexListResource::collection(
                Index::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'sections' => SectionListResource::collection(
                Section::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'pages' => PageListResource::collection(
                Page::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
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

    public function publish(Request $request, Batch $batch)
    {
        $batch->loadMissing(['approval', ...$batch->batchables]);
        if (! $batch->approval?->approved_at) {
            return redirect()->back()->withMessage($batch->title.' must be approved before it can be published.', messageType: MessageTypeEnum::destructive);
        }

        $itemNeedsApproval = false;
        foreach ($batch->batchables as $batchable) {
            $batch->$batchable->each(function ($batchable) use ($request, &$itemNeedsApproval) {
                $batchable->loadMissing('approval');
                if (! $batchable->approval?->approved_at) {
                    $itemNeedsApproval = true;
                } else {
                    $batchable->publish($request->user());
                }
            });
        }

        if ($itemNeedsApproval) {
            return to_route('admin.batches.index')->withMessage('All Batch Items Must Be Approved Before Publishing', messageType: MessageTypeEnum::destructive);
        }

        $batch->update([
            'published_at' => now(),
            'published_by' => $request->user()->id,
        ]);

        return to_route('admin.batches.index')->withMessage($batch->title.' has been published!');
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
            $batch->approval?->delete();
            CreateApprovalAction::handle($batch->refresh(), $request->user());
        }

        return $batch;
    }
}
