<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\MessageTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\IndexListResource;
use App\Http\Resources\PageListResource;
use App\Http\Resources\SeasonListResource;
use App\Http\Resources\SectionListResource;
use App\Models\Batch;
use App\Models\Index;
use App\Models\Page;
use App\Models\Season;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;

class SeasonAdminController extends Controller
{
    public function list(Request $request)
    {
        return SeasonListResource::collection(
            Season::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
        )->toArray($request);
    }

    public function preview(Request $request)
    {
        $content = $request->get('content') ?? '';
        $changeNotes = $request->get('change_notes') ?? null;

        return [
            'title' => $request->get('title') ?? '',
            'content' => (new ContentBuilder($content))->getFullyHydratedContent(),
            'change_notes' => $changeNotes ? (new ContentBuilder($changeNotes))->getFullyHydratedContent() : null,
        ];
    }

    public function view(Request $request, Season $season)
    {
        $season->loadMissing('newestVersion', 'publishedBy');

        $content = (new ContentBuilder($season->content ?? ''))->getFullyHydratedContent();

        return [
            'title' => $season->title,
            'content' => $content,
            'published_at' => $season->published_at?->format('m-d-Y'),
            'published_by' => $season->publishedBy?->name,
        ];
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Seasons/Index', [
            'seasons' => Season::with('approval', 'batch')
                ->orderBy('id', 'DESC')
                ->orderBy('title', 'ASC')
                ->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Seasons/SeasonForm', [
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
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

    public function edit(Request $request, Season $season): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Seasons/SeasonForm', [
            'season' => $season->loadMissing('approval'),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
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

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $season = $this->validateAndSave($request);

        return to_route('admin.seasons.index')->withMessage($season->title.' created successfully!');
    }

    public function update(Request $request, Season $season): \Illuminate\Http\RedirectResponse
    {
        $season = $this->validateAndSave($request, $season);

        return to_route('admin.seasons.index')->withMessage($season->title.' updated successfully!');
    }

    public function delete(Request $request, Season $season): \Illuminate\Http\RedirectResponse
    {
        $name = $season->title;

        $season->delete();

        return to_route('admin.seasons.index')->withMessage($name.' has been deleted.');
    }

    public function publish(Request $request, Season $season): \Illuminate\Http\RedirectResponse
    {
        try {
            $season->publish($request->user());
        } catch (\Exception $exception) {
            return redirect()->back()->withMessage($exception->getMessage(), messageType: MessageTypeEnum::destructive);
        }

        return to_route('admin.seasons.index')->withMessage($season->title.' has been published!');
    }

    private function validateAndSave(Request $request, ?Season $season = null): Season
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'url' => ['nullable', 'string'],
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
        $changeNotes = preg_replace("/(\r|\n)/", '', nl2br($validated['change_notes']));
        unset($validated['change_notes']);

        if ($validated['content']) {
            $validated['content'] = preg_replace("/(\r|\n)/", '', nl2br($validated['content']));
        }

        if (! $season) {
            $season = Season::create($validated);
        } else {
            $season->loadMissing('approval');

            if (! $season->published_at) {
                $season->update($validated);
                $season->approval?->delete();
            } else {
                $validated['previous'] = $season->id;
                $validated['original'] = $season->original ?? $season->id;
                $season = Season::create($validated);
            }
        }

        CreateApprovalAction::handle(
            $season->refresh(),
            $request->user(),
            changeNotes: $changeNotes,
            approveDirectly: $approveDirectly
        );

        if ($publishDirectly) {
            self::publish($request, $season->refresh());
        }

        return $season;
    }
}
