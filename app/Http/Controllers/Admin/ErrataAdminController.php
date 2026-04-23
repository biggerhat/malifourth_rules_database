<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\MessageTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ErrataListResource;
use App\Models\Batch;
use App\Models\Errata;
use App\Services\ContentBuilder\ContentBuilder;
use App\Traits\HandlesBulkActions;
use Illuminate\Http\Request;

class ErrataAdminController extends Controller
{
    use HandlesBulkActions;

    protected function bulkModel(): string
    {
        return Errata::class;
    }

    protected function bulkLabel(): string
    {
        return 'errata item(s)';
    }

    public function list(Request $request)
    {
        return ErrataListResource::collection(
            Errata::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
        )->toArray($request);
    }

    public function view(Request $request, Errata $errata)
    {
        $errata->loadMissing('newestVersion', 'publishedBy');

        return [
            'title' => $errata->title,
            'content' => (new ContentBuilder($errata->content ?? ''))->getFullyHydratedContent(),
            'published_at' => $errata->published_at?->format('m-d-Y'),
            'published_by' => $errata->publishedBy?->name,
        ];
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Errata/Index', [
            'errataItems' => Errata::with('approval', 'batch')
                ->orderBy('id', 'DESC')
                ->orderBy('title', 'ASC')
                ->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Errata/ErrataForm', [
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
        ]);
    }

    public function edit(Request $request, Errata $errata): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Errata/ErrataForm', [
            'errata' => $errata->loadMissing('approval'),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $errata = $this->validateAndSave($request);

        return to_route('admin.errata.index')->withMessage($errata->title.' created successfully!');
    }

    public function update(Request $request, Errata $errata): \Illuminate\Http\RedirectResponse
    {
        $errata = $this->validateAndSave($request, $errata);

        return to_route('admin.errata.index')->withMessage($errata->title.' updated successfully!');
    }

    public function delete(Request $request, Errata $errata): \Illuminate\Http\RedirectResponse
    {
        $name = $errata->title;

        $errata->delete();

        return to_route('admin.errata.index')->withMessage($name.' has been deleted.');
    }

    public function publish(Request $request, Errata $errata): \Illuminate\Http\RedirectResponse
    {
        try {
            $errata->publish($request->user());
        } catch (\Exception $exception) {
            return redirect()->back()->withMessage($exception->getMessage(), messageType: MessageTypeEnum::destructive);
        }

        return to_route('admin.errata.index')->withMessage($errata->title.' has been published!');
    }

    private function validateAndSave(Request $request, ?Errata $errata = null): Errata
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
        $changeNotes = ContentBuilder::detectTipTapJson($validated['change_notes'] ?? '')
            ? $validated['change_notes']
            : preg_replace("/(\r|\n)/", '', nl2br($validated['change_notes']));
        unset($validated['change_notes']);

        if ($validated['content'] && ! ContentBuilder::detectTipTapJson($validated['content'])) {
            $validated['content'] = preg_replace("/(\r|\n)/", '', nl2br($validated['content']));
        }

        if (! $errata) {
            $errata = Errata::create($validated);
        } else {
            $errata->loadMissing('approval');

            if (! $errata->published_at) {
                $errata->update($validated);
                $errata->approval?->delete();
            } else {
                $validated['previous'] = $errata->id;
                $validated['original'] = $errata->original ?? $errata->id;
                $errata = Errata::create($validated);
            }
        }

        CreateApprovalAction::handle(
            $errata->refresh(),
            $request->user(),
            changeNotes: $changeNotes,
            approveDirectly: $approveDirectly
        );

        if ($publishDirectly) {
            self::publish($request, $errata->refresh());
        }

        return $errata;
    }
}
