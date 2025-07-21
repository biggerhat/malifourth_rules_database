<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\MessageTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\SectionListResource;
use App\Models\Batch;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;

class SectionAdminController extends Controller
{
    public function list(Request $request)
    {
        return SectionListResource::collection(
            Section::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
        )->toArray($request);
    }

    public function view(Request $request, Section $section)
    {
        $section->loadMissing('newestVersion', 'publishedBy');

        $content = (new ContentBuilder($section->content ?? ''))->getFullyHydratedContent();

        return [
            'title' => $section->title,
            'content' => $content,
            'published_at' => $section->published_at?->format('m-d-Y'),
            'published_by' => $section->publishedBy?->name,
        ];
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Sections/Index', [
            'sections' => Section::with('approval', 'batch')
                ->orderBy('id', 'DESC')
                ->orderBy('title', 'ASC')
                ->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Sections/SectionForm', [
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
        ]);
    }

    public function edit(Request $request, Section $section): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Sections/SectionForm', [
            'section' => $section->loadMissing('approval'),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $section = $this->validateAndSave($request);

        return to_route('admin.sections.index')->withMessage($section->title.' created successfully!');
    }

    public function update(Request $request, Section $section): \Illuminate\Http\RedirectResponse
    {
        $section = $this->validateAndSave($request, $section);

        return to_route('admin.sections.index')->withMessage($section->title.' updated successfully!');
    }

    public function delete(Request $request, Section $section): \Illuminate\Http\RedirectResponse
    {
        $name = $section->title;

        $section->delete();

        return to_route('admin.sections.index')->withMessage($name.' has been deleted.');
    }

    public function publish(Request $request, Section $section): \Illuminate\Http\RedirectResponse
    {
        try {
            $section->publish($request->user());
        } catch (\Exception $exception) {
            return redirect()->back()->withMessage($exception->getMessage(), messageType: MessageTypeEnum::destructive);
        }

        return to_route('admin.sections.index')->withMessage($section->title.' has been published!');
    }

    private function validateAndSave(Request $request, ?Section $section = null): Section
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'change_notes' => ['nullable', 'string'],
            'batch_id' => ['nullable', 'int', 'exists:batches,id'],
            'publish_directly' => ['required', 'boolean'],
            'approve_directly' => ['required', 'boolean'],
        ]);

        $publishDirectly = $validated['publish_directly'];
        unset($validated['publish_directly']);
        $approveDirectly = $validated['approve_directly'];
        unset($validated['approve_directly']);
        $changeNotes = $validated['change_notes'];
        unset($validated['change_notes']);

        if (! $section) {
            $section = Section::create($validated);
        } else {
            $section->loadMissing('approval');

            if (! $section->published_at) {
                $section->update($validated);
                $section->approval?->delete();
            } else {
                $validated['previous'] = $section->id;
                $validated['original'] = $section->original ?? $section->id;
                $section = Section::create($validated);
            }
        }

        CreateApprovalAction::handle(
            $section->refresh(),
            $request->user(),
            changeNotes: $changeNotes,
            approveDirectly: $approveDirectly
        );

        if ($publishDirectly) {
            self::publish($request, $section->refresh());
        }

        return $section;
    }
}
