<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\FaqCategoryEnum;
use App\Enums\MessageTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\FaqListResource;
use App\Models\Batch;
use App\Models\Faq;
use App\Services\ContentBuilder\ContentBuilder;
use App\Traits\HandlesBulkActions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FaqAdminController extends Controller
{
    use HandlesBulkActions;

    protected function bulkModel(): string
    {
        return Faq::class;
    }

    protected function bulkLabel(): string
    {
        return 'FAQ(s)';
    }

    public function list(Request $request)
    {
        return FaqListResource::collection(
            Faq::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
        )->toArray($request);
    }

    public function view(Request $request, Faq $faq)
    {
        $faq->loadMissing('newestVersion', 'publishedBy');

        $answer = (new ContentBuilder($faq->answer ?? ''))->getFullyHydratedContent();

        return [
            'faq' => [
                'title' => (new ContentBuilder($faq->title))->getFullyHydratedContent(),
                'title_text' => ContentBuilder::toPlainText($faq->title),
                'category' => $faq->category->value,
                'category_label' => $faq->category->label(),
                'answer' => $answer,
                'published_at' => $faq->published_at?->format('m-d-Y'),
                'published_by' => $faq->publishedBy?->name,
            ],
        ];
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Faqs/Index', [
            'faqs' => Faq::with('approval', 'batch')
                ->orderBy('id', 'DESC')
                ->orderBy('title', 'ASC')
                ->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Faqs/FaqForm', [
            'faq_categories' => FaqCategoryEnum::toSelectOptions(),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
        ]);
    }

    public function edit(Request $request, Faq $faq): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Faqs/FaqForm', [
            'faq' => $faq->loadMissing('approval'),
            'faq_categories' => FaqCategoryEnum::toSelectOptions(),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $faq = $this->validateAndSave($request);

        return to_route('admin.faqs.index')->withMessage($faq->title.' created successfully!');
    }

    public function update(Request $request, Faq $faq): \Illuminate\Http\RedirectResponse
    {
        $faq = $this->validateAndSave($request, $faq);

        return to_route('admin.faqs.index')->withMessage($faq->title.' updated successfully!');
    }

    public function delete(Request $request, Faq $faq): \Illuminate\Http\RedirectResponse
    {
        $name = $faq->title;

        $faq->delete();

        return to_route('admin.faqs.index')->withMessage($name.' has been deleted.');
    }

    public function publish(Request $request, Faq $faq): \Illuminate\Http\RedirectResponse
    {
        try {
            $faq->publish($request->user());
        } catch (\Exception $exception) {
            return redirect()->back()->withMessage($exception->getMessage(), messageType: MessageTypeEnum::destructive);
        }

        return to_route('admin.faqs.index')->withMessage($faq->title.' has been published!');
    }

    private function validateAndSave(Request $request, ?Faq $faq = null): Faq
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'category' => ['required', 'string', Rule::enum(FaqCategoryEnum::class)],
            'answer' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer'],
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

        if (! ContentBuilder::detectTipTapJson($validated['title'] ?? '')) {
            $validated['title'] = preg_replace("/(\r|\n)/", '', $validated['title']);
        }

        if ($validated['answer'] && ! ContentBuilder::detectTipTapJson($validated['answer'])) {
            $validated['answer'] = preg_replace("/(\r|\n)/", '', nl2br($validated['answer']));
        }

        if (! $faq) {
            $faq = Faq::create($validated);
        } else {
            $faq->loadMissing('approval');

            if (! $faq->published_at) {
                $faq->update($validated);
                $faq->approval?->delete();
            } else {
                $validated['previous'] = $faq->id;
                $validated['original'] = $faq->original ?? $faq->id;
                $faq = Faq::create($validated);
            }
        }

        CreateApprovalAction::handle(
            $faq->refresh(),
            $request->user(),
            changeNotes: $changeNotes,
            approveDirectly: $approveDirectly
        );

        if ($publishDirectly) {
            self::publish($request, $faq->refresh());
        }

        return $faq;
    }
}
