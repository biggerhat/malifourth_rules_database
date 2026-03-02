<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\MessageTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\FaqListResource;
use App\Http\Resources\IndexListResource;
use App\Http\Resources\PageListResource;
use App\Http\Resources\SchemeListResource;
use App\Http\Resources\SeasonListResource;
use App\Http\Resources\SectionListResource;
use App\Models\Batch;
use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Scheme;
use App\Models\Season;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Str;

class SchemeAdminController extends Controller
{
    public function list(Request $request)
    {
        return SchemeListResource::collection(
            Scheme::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
        )->toArray($request);
    }

    public function preview(Request $request)
    {
        $prerequisites = $request->get('prerequisites') ?? '';
        $reveal = $request->get('reveal') ?? '';
        $scoring = $request->get('scoring') ?? '';
        $additional = $request->get('additional') ?? '';
        $changeNotes = $request->get('change_notes') ?? null;

        return [
            'title' => $request->get('title') ?? '',
            'prerequisites' => (new ContentBuilder($prerequisites))->getFullyHydratedContent(),
            'reveal' => (new ContentBuilder($reveal))->getFullyHydratedContent(),
            'scoring' => (new ContentBuilder($scoring))->getFullyHydratedContent(),
            'additional' => (new ContentBuilder($additional))->getFullyHydratedContent(),
            'change_notes' => $changeNotes ? (new ContentBuilder($changeNotes))->getFullyHydratedContent() : null,
        ];
    }

    public function view(Request $request, Scheme $scheme)
    {
        $scheme->loadMissing('newestVersion', 'publishedBy');

        return [
            'title' => $scheme->title,
            'prerequisites' => (new ContentBuilder($scheme->prerequisites ?? ''))->getFullyHydratedContent(),
            'reveal' => (new ContentBuilder($scheme->reveal ?? ''))->getFullyHydratedContent(),
            'scoring' => (new ContentBuilder($scheme->scoring ?? ''))->getFullyHydratedContent(),
            'additional' => (new ContentBuilder($scheme->additional ?? ''))->getFullyHydratedContent(),
            'published_at' => $scheme->published_at?->format('m-d-Y'),
            'published_by' => $scheme->publishedBy?->name,
        ];
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Schemes/Index', [
            'schemes' => Scheme::with('approval', 'batch', 'season')
                ->orderBy('id', 'DESC')
                ->orderBy('title', 'ASC')
                ->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Schemes/SchemeForm', [
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
            'seasons' => SeasonListResource::collection(
                Season::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'schemes' => SchemeListResource::collection(
                Scheme::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
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

    public function edit(Request $request, Scheme $scheme): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Schemes/SchemeForm', [
            'scheme' => $scheme->loadMissing('approval'),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
            'seasons' => SeasonListResource::collection(
                Season::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'schemes' => SchemeListResource::collection(
                Scheme::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
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
        $scheme = $this->validateAndSave($request);

        return to_route('admin.schemes.index')->withMessage($scheme->title.' created successfully!');
    }

    public function update(Request $request, Scheme $scheme): \Illuminate\Http\RedirectResponse
    {
        $scheme = $this->validateAndSave($request, $scheme);

        return to_route('admin.schemes.index')->withMessage($scheme->title.' updated successfully!');
    }

    public function delete(Request $request, Scheme $scheme): \Illuminate\Http\RedirectResponse
    {
        $name = $scheme->title;

        $scheme->delete();

        return to_route('admin.schemes.index')->withMessage($name.' has been deleted.');
    }

    public function publish(Request $request, Scheme $scheme): \Illuminate\Http\RedirectResponse
    {
        try {
            $scheme->publish($request->user());
        } catch (\Exception $exception) {
            return redirect()->back()->withMessage($exception->getMessage(), messageType: MessageTypeEnum::destructive);
        }

        return to_route('admin.schemes.index')->withMessage($scheme->title.' has been published!');
    }

    public function bulkApprove(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = Scheme::with('approval')->whereIn('id', $validated['ids'])->get();
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

        return redirect()->back()->withMessage("{$count} scheme(s) approved.");
    }

    public function bulkPublish(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = Scheme::with('approval')->whereIn('id', $validated['ids'])->get();
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

        $message = "{$count} scheme(s) published.";
        if (! empty($errors)) {
            $message .= ' Errors: '.implode('; ', $errors);
        }

        return redirect()->back()->withMessage($message);
    }

    public function bulkDelete(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $count = Scheme::whereIn('id', $validated['ids'])->whereNull('published_at')->delete();

        return redirect()->back()->withMessage("{$count} scheme(s) deleted.");
    }

    private function validateAndSave(Request $request, ?Scheme $scheme = null): Scheme
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'season_id' => ['required', 'integer', 'exists:seasons,id'],
            'prerequisites' => ['nullable', 'string'],
            'reveal' => ['nullable', 'string'],
            'scoring' => ['nullable', 'string'],
            'additional' => ['nullable', 'string'],
            'front_image' => ['nullable', 'file', 'max:30000', 'mimes:heic,jpeg,jpg,png,webp'],
            'back_image' => ['nullable', 'file', 'max:30000', 'mimes:heic,jpeg,jpg,png,webp'],
            'combination_image' => ['nullable', 'file', 'max:30000', 'mimes:heic,jpeg,jpg,png,webp'],
            'next_scheme_1' => ['nullable', 'integer', 'exists:schemes,id'],
            'next_scheme_2' => ['nullable', 'integer', 'exists:schemes,id'],
            'next_scheme_3' => ['nullable', 'integer', 'exists:schemes,id'],
            'internal_notes' => ['nullable', 'string'],
            'change_notes' => ['nullable', 'string'],
            'batch_id' => ['nullable', 'int', 'exists:batches,id'],
            'publish_directly' => ['required', 'boolean'],
            'approve_directly' => ['required', 'boolean'],
        ]);

        $existingImages = [
            'front_image' => $scheme?->front_image,
            'back_image' => $scheme?->back_image,
            'combination_image' => $scheme?->combination_image,
        ];

        $publishDirectly = $validated['publish_directly'];
        unset($validated['publish_directly']);
        $approveDirectly = $validated['approve_directly'];
        unset($validated['approve_directly']);
        $changeNotes = preg_replace("/(\r|\n)/", '', nl2br($validated['change_notes']));
        unset($validated['change_notes']);

        foreach (['prerequisites', 'reveal', 'scoring', 'additional'] as $field) {
            if ($validated[$field]) {
                $validated[$field] = preg_replace("/(\r|\n)/", '', nl2br($validated[$field]));
            }
        }

        foreach (['front_image', 'back_image', 'combination_image'] as $imageField) {
            if (isset($validated[$imageField]) && $validated[$imageField]) {
                $nameSlug = Str::slug($validated['title']);
                $extension = $validated[$imageField]->extension();
                $uuid = Str::uuid();
                $fileName = sprintf('%s_%s.%s', $nameSlug, $uuid, $extension);
                $filePath = "schemes/{$nameSlug}/{$fileName}";
                Storage::disk('public')->put($filePath, file_get_contents($validated[$imageField]));
                $validated[$imageField] = '/storage/'.$filePath;
            } else {
                unset($validated[$imageField]);
            }
        }

        if (! $scheme) {
            $scheme = Scheme::create($validated);
        } else {
            $scheme->loadMissing('approval');

            if (! $scheme->published_at) {
                $scheme->update($validated);
                $scheme->approval?->delete();
            } else {
                $validated['previous'] = $scheme->id;
                $validated['original'] = $scheme->original ?? $scheme->id;
                foreach ($existingImages as $key => $value) {
                    $validated[$key] = $validated[$key] ?? $value;
                }
                $scheme = Scheme::create($validated);
            }
        }

        CreateApprovalAction::handle(
            $scheme->refresh(),
            $request->user(),
            changeNotes: $changeNotes,
            approveDirectly: $approveDirectly
        );

        if ($publishDirectly) {
            self::publish($request, $scheme->refresh());
        }

        return $scheme;
    }
}
