<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\MessageTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\SeasonListResource;
use App\Http\Resources\SeasonPageListResource;
use App\Models\Batch;
use App\Models\Season;
use App\Models\SeasonPage;
use App\Services\ContentBuilder\ContentBuilder;
use App\Traits\HandlesBulkActions;
use Illuminate\Http\Request;

class SeasonPageAdminController extends Controller
{
    use HandlesBulkActions;

    protected function bulkModel(): string
    {
        return SeasonPage::class;
    }

    protected function bulkLabel(): string
    {
        return 'season page(s)';
    }

    public function list(Request $request)
    {
        return SeasonPageListResource::collection(
            SeasonPage::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
        )->toArray($request);
    }

    public function view(Request $request, SeasonPage $seasonPage)
    {
        $seasonPage->loadMissing('newestVersion', 'publishedBy');

        return [
            'title' => $seasonPage->title,
            'content' => (new ContentBuilder($seasonPage->content ?? ''))->getFullyHydratedContent(),
            'published_at' => $seasonPage->published_at?->format('m-d-Y'),
            'published_by' => $seasonPage->publishedBy?->name,
        ];
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/SeasonPages/Index', [
            'seasonPages' => SeasonPage::with('approval', 'batch', 'season')
                ->orderBy('id', 'DESC')
                ->orderBy('title', 'ASC')
                ->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/SeasonPages/SeasonPageForm', [
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
            'seasons' => SeasonListResource::collection(
                Season::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
        ]);
    }

    public function edit(Request $request, SeasonPage $seasonPage): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/SeasonPages/SeasonPageForm', [
            'seasonPage' => $seasonPage->loadMissing('approval'),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
            'seasons' => SeasonListResource::collection(
                Season::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $seasonPage = $this->validateAndSave($request);

        return to_route('admin.season-pages.index')->withMessage($seasonPage->title.' created successfully!');
    }

    public function update(Request $request, SeasonPage $seasonPage): \Illuminate\Http\RedirectResponse
    {
        $seasonPage = $this->validateAndSave($request, $seasonPage);

        return to_route('admin.season-pages.index')->withMessage($seasonPage->title.' updated successfully!');
    }

    public function delete(Request $request, SeasonPage $seasonPage): \Illuminate\Http\RedirectResponse
    {
        $name = $seasonPage->title;

        $seasonPage->delete();

        return to_route('admin.season-pages.index')->withMessage($name.' has been deleted.');
    }

    public function publish(Request $request, SeasonPage $seasonPage): \Illuminate\Http\RedirectResponse
    {
        try {
            $seasonPage->publish($request->user());
        } catch (\Exception $exception) {
            return redirect()->back()->withMessage($exception->getMessage(), messageType: MessageTypeEnum::destructive);
        }

        return to_route('admin.season-pages.index')->withMessage($seasonPage->title.' has been published!');
    }

    private function validateAndSave(Request $request, ?SeasonPage $seasonPage = null): SeasonPage
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'season_id' => ['required', 'integer', 'exists:seasons,id'],
            'sort_order' => ['required', 'integer', 'min:0'],
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
        $changeNotes = ContentBuilder::detectTipTapJson($validated['change_notes'] ?? '')
            ? $validated['change_notes']
            : preg_replace("/(\r|\n)/", '', nl2br($validated['change_notes']));
        unset($validated['change_notes']);

        if ($validated['content'] && ! ContentBuilder::detectTipTapJson($validated['content'])) {
            $validated['content'] = preg_replace("/(\r|\n)/", '', nl2br($validated['content']));
        }

        if (! $seasonPage) {
            $seasonPage = SeasonPage::create($validated);
        } else {
            $seasonPage->loadMissing('approval');

            if (! $seasonPage->published_at) {
                $seasonPage->update($validated);
                $seasonPage->approval?->delete();
            } else {
                $validated['previous'] = $seasonPage->id;
                $validated['original'] = $seasonPage->original ?? $seasonPage->id;
                $seasonPage = SeasonPage::create($validated);
            }
        }

        CreateApprovalAction::handle(
            $seasonPage->refresh(),
            $request->user(),
            changeNotes: $changeNotes,
            approveDirectly: $approveDirectly
        );

        if ($publishDirectly) {
            self::publish($request, $seasonPage->refresh());
        }

        return $seasonPage;
    }
}
