<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\MessageTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ErrataListResource;
use App\Http\Resources\FaqListResource;
use App\Http\Resources\IndexListResource;
use App\Http\Resources\PageListResource;
use App\Http\Resources\SectionListResource;
use App\Models\Batch;
use App\Models\Errata;
use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;

class ErrataAdminController extends Controller
{
    public function list(Request $request)
    {
        return ErrataListResource::collection(
            Errata::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
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

    public function edit(Request $request, Errata $errata): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Errata/ErrataForm', [
            'errata' => $errata->loadMissing('approval'),
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

    public function bulkApprove(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = Errata::with('approval')->whereIn('id', $validated['ids'])->get();
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

        return redirect()->back()->withMessage("{$count} errata item(s) approved.");
    }

    public function bulkPublish(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = Errata::with('approval')->whereIn('id', $validated['ids'])->get();
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

        $message = "{$count} errata item(s) published.";
        if (! empty($errors)) {
            $message .= ' Errors: '.implode('; ', $errors);
        }

        return redirect()->back()->withMessage($message);
    }

    public function bulkDelete(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $count = Errata::whereIn('id', $validated['ids'])->whereNull('published_at')->delete();

        return redirect()->back()->withMessage("{$count} errata item(s) deleted.");
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
        $changeNotes = preg_replace("/(\r|\n)/", '', nl2br($validated['change_notes']));
        unset($validated['change_notes']);

        if ($validated['content']) {
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
