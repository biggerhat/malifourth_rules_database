<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\MessageTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\IndexListResource;
use App\Http\Resources\PageListResource;
use App\Http\Resources\SectionListResource;
use App\Models\Batch;
use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;

class PageAdminController extends Controller
{
    public function list(Request $request)
    {
        return PageListResource::collection(
            Page::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
        )->toArray($request);
    }

    public function view(Request $request, Page $page)
    {
        $page->loadMissing('newestVersion', 'publishedBy');

        $leftContent = (new ContentBuilder($page->left_column ?? ''))->getFullyHydratedContent();
        $rightContent = (new ContentBuilder($page->right_column ?? ''))->getFullyHydratedContent();

        return [
            'title' => $page->title,
            'left_column' => $leftContent,
            'right_column' => $rightContent,
            'page_number' => $page->page_number,
            'book_page_numbers' => $page->book_page_numbers,
            'published_at' => $page->published_at?->format('m-d-Y'),
            'published_by' => $page->publishedBy?->name,
        ];
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Pages/Index', [
            'pages' => Page::with('approval', 'batch')
                ->orderBy('id', 'DESC')
                ->orderBy('title', 'ASC')
                ->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Pages/PageForm', [
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

    public function edit(Request $request, Page $page): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Pages/PageForm', [
            'page' => $page->loadMissing('approval'),
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
        $page = $this->validateAndSave($request);

        return to_route('admin.pages.index')->withMessage($page->title.' created successfully!');
    }

    public function update(Request $request, Page $page): \Illuminate\Http\RedirectResponse
    {
        $page = $this->validateAndSave($request, $page);

        return to_route('admin.pages.index')->withMessage($page->title.' updated successfully!');
    }

    public function delete(Request $request, Page $page): \Illuminate\Http\RedirectResponse
    {
        $name = $page->title;

        $page->delete();

        return to_route('admin.pages.index')->withMessage($name.' has been deleted.');
    }

    public function publish(Request $request, Page $page): \Illuminate\Http\RedirectResponse
    {
        try {
            $page->publish($request->user());
        } catch (\Exception $exception) {
            return redirect()->back()->withMessage($exception->getMessage(), messageType: MessageTypeEnum::destructive);
        }

        return to_route('admin.pages.index')->withMessage($page->title.' has been published!');
    }

    private function validateAndSave(Request $request, ?Page $page = null): Page
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'left_column' => ['nullable', 'string'],
            'right_column' => ['nullable', 'string'],
            'book_page_numbers' => ['nullable', 'string'],
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
        $changeNotes = $validated['change_notes'] ?? null;
        unset($validated['change_notes']);

        $highestPage = Page::orderBy('page_number', 'DESC')->first();
        if ($highestPage) {
            $validated['page_number'] = $highestPage->page_number + 1;
        } else {
            $validated['page_number'] = 1;
        }

        if (! $page) {
            $page = Page::create($validated);
        } else {
            $page->loadMissing('approval');

            if (! $page->published_at) {
                $page->update($validated);
                $page->approval?->delete();
            } else {
                $validated['previous'] = $page->id;
                $validated['original'] = $page->original ?? $page->id;
                $page = Page::create($validated);
            }
        }

        CreateApprovalAction::handle(
            $page->refresh(),
            $request->user(),
            changeNotes: $changeNotes,
            approveDirectly: $approveDirectly
        );

        if ($publishDirectly) {
            self::publish($request, $page->refresh());
        }

        return $page;
    }
}
