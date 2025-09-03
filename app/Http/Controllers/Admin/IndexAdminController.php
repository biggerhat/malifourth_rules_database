<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\IndexTypeEnum;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Str;

class IndexAdminController extends Controller
{
    public function list(Request $request)
    {
        return IndexListResource::collection(
            Index::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
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

    public function view(Request $request, Index $index)
    {
        $index->loadMissing('newestVersion', 'publishedBy');

        $content = (new ContentBuilder($index->content ?? ''))->getFullyHydratedContent();

        return [
            'title' => $index->title,
            'type' => $index->type->value,
            'content' => $content,
            'image' => $index->image,
            'published_at' => $index->published_at?->format('m-d-Y'),
            'published_by' => $index->publishedBy?->name,
        ];
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Indices/Index', [
            'indices' => Index::with('approval', 'batch')
                ->orderBy('id', 'DESC')
                ->orderBy('title', 'ASC')
                ->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Indices/IndexForm', [
            'index_types' => IndexTypeEnum::toSelectOptions(),
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

    public function edit(Request $request, Index $index): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Indices/IndexForm', [
            'index' => $index->loadMissing('approval'),
            'index_types' => IndexTypeEnum::toSelectOptions(),
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
        $index = $this->validateAndSave($request);

        return to_route('admin.indices.index')->withMessage($index->title.' created successfully!');
    }

    public function update(Request $request, Index $index): \Illuminate\Http\RedirectResponse
    {
        $index = $this->validateAndSave($request, $index);

        return to_route('admin.indices.index')->withMessage($index->title.' updated successfully!');
    }

    public function delete(Request $request, Index $index): \Illuminate\Http\RedirectResponse
    {
        $name = $index->title;

        $index->delete();

        return to_route('admin.indices.index')->withMessage($name.' has been deleted.');
    }

    public function publish(Request $request, Index $index): \Illuminate\Http\RedirectResponse
    {
        try {
            $index->publish($request->user());
        } catch (\Exception $exception) {
            return redirect()->back()->withMessage($exception->getMessage(), messageType: MessageTypeEnum::destructive);
        }

        return to_route('admin.indices.index')->withMessage($index->title.' has been published!');
    }

    private function validateAndSave(Request $request, ?Index $index = null): Index
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::enum(IndexTypeEnum::class)],
            'image' => ['nullable', 'file', 'max:30000', 'mimes:heic,jpeg,jpg,png,webp'],
            'content' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'change_notes' => ['nullable', 'string'],
            'batch_id' => ['nullable', 'int', 'exists:batches,id'],
            'publish_directly' => ['required', 'boolean'],
            'approve_directly' => ['required', 'boolean'],
        ]);

        $imagePath = $index?->image;
        $publishDirectly = $validated['publish_directly'];
        unset($validated['publish_directly']);
        $approveDirectly = $validated['approve_directly'];
        unset($validated['approve_directly']);
        $changeNotes = preg_replace("/(\r|\n)/", '', nl2br($validated['change_notes']));
        unset($validated['change_notes']);

        if ($validated['content']) {
            $validated['content'] = preg_replace("/(\r|\n)/", '', nl2br($validated['content']));
        }

        if ($validated['image']) {
            $nameSlug = Str::slug($validated['title']);
            $extension = $validated['image']->extension();
            $uuid = Str::uuid();
            $fileName = sprintf('%s_%s.%s', Str::slug($nameSlug), $uuid, $extension);
            $filePath = "indices/{$nameSlug}/{$fileName}";
            Storage::disk('public')->put($filePath, file_get_contents($validated['image']));
            $validated['image'] = '/storage/'.$filePath;
        } else {
            unset($validated['image']);
        }

        if (! $index) {
            $index = Index::create($validated);
        } else {
            $index->loadMissing('approval');

            if (! $index->published_at) {
                $index->update($validated);
                $index->approval?->delete();
            } else {
                $validated['previous'] = $index->id;
                $validated['original'] = $index->original ?? $index->id;
                $validated['image'] = $validated['image'] ?? $imagePath;
                $index = Index::create($validated);
            }
        }

        CreateApprovalAction::handle(
            $index->refresh(),
            $request->user(),
            changeNotes: $changeNotes,
            approveDirectly: $approveDirectly
        );

        if ($publishDirectly) {
            self::publish($request, $index->refresh());
        }

        return $index;
    }
}
