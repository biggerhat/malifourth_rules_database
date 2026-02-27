<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\FaqCategoryEnum;
use App\Enums\MessageTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\FaqListResource;
use App\Http\Resources\IndexListResource;
use App\Http\Resources\PageListResource;
use App\Http\Resources\SectionListResource;
use App\Models\Batch;
use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FaqAdminController extends Controller
{
    public function list(Request $request)
    {
        return FaqListResource::collection(
            Faq::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
        )->toArray($request);
    }

    public function preview(Request $request)
    {
        $title = $request->get('title') ?? '';
        $answer = $request->get('answer') ?? '';
        $changeNotes = $request->get('change_notes') ?? null;

        return [
            'title' => (new ContentBuilder($title))->getFullyHydratedContent(),
            'answer' => (new ContentBuilder($answer))->getFullyHydratedContent(),
            'change_notes' => $changeNotes ? (new ContentBuilder($changeNotes))->getFullyHydratedContent() : null,
        ];
    }

    public function view(Request $request, Faq $faq)
    {
        $faq->loadMissing('newestVersion', 'publishedBy');

        $answer = (new ContentBuilder($faq->answer ?? ''))->getFullyHydratedContent();

        return [
            'faq' => [
                'title' => (new ContentBuilder($faq->title))->getFullyHydratedContent(),
                'title_text' => ContentBuilder::toSearchable($faq->title),
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
            'indices' => IndexListResource::collection(
                Index::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'sections' => SectionListResource::collection(
                Section::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'pages' => PageListResource::collection(
                Page::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'faqs' => FaqListResource::collection(
                Faq::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
        ]);
    }

    public function edit(Request $request, Faq $faq): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Faqs/FaqForm', [
            'faq' => $faq->loadMissing('approval'),
            'faq_categories' => FaqCategoryEnum::toSelectOptions(),
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
            'faqs' => FaqListResource::collection(
                Faq::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
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

    public function bulkApprove(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = Faq::with('approval')->whereIn('id', $validated['ids'])->get();
        $count = 0;

        foreach ($items as $item) {
            if ($item->approval && ! $item->approval->approved_at) {
                $item->approval->update([
                    'approved_at' => now(),
                    'approved_by' => $request->user()->id,
                ]);
                $count++;
            }
        }

        return redirect()->back()->withMessage("{$count} FAQ(s) approved.");
    }

    public function bulkPublish(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = Faq::with('approval')->whereIn('id', $validated['ids'])->get();
        $count = 0;
        $errors = [];

        foreach ($items as $item) {
            try {
                $item->publish($request->user());
                $count++;
            } catch (\Exception $e) {
                $errors[] = $item->title.': '.$e->getMessage();
            }
        }

        $message = "{$count} FAQ(s) published.";
        if (! empty($errors)) {
            $message .= ' Errors: '.implode('; ', $errors);
        }

        return redirect()->back()->withMessage($message);
    }

    public function bulkDelete(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $count = Faq::whereIn('id', $validated['ids'])->whereNull('published_at')->delete();

        return redirect()->back()->withMessage("{$count} FAQ(s) deleted.");
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
        $changeNotes = preg_replace("/(\r|\n)/", '', nl2br($validated['change_notes']));
        unset($validated['change_notes']);

        $validated['title'] = preg_replace("/(\r|\n)/", '', $validated['title']);

        if ($validated['answer']) {
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
