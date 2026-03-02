<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\MessageTypeEnum;
use App\Enums\SuitEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\FaqListResource;
use App\Http\Resources\IndexListResource;
use App\Http\Resources\PageListResource;
use App\Http\Resources\SeasonListResource;
use App\Http\Resources\SectionListResource;
use App\Http\Resources\StrategyListResource;
use App\Models\Batch;
use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Season;
use App\Models\Section;
use App\Models\Strategy;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Str;

class StrategyAdminController extends Controller
{
    public function list(Request $request)
    {
        return StrategyListResource::collection(
            Strategy::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
        )->toArray($request);
    }

    public function preview(Request $request)
    {
        $setup = $request->get('setup') ?? '';
        $rules = $request->get('rules') ?? '';
        $scoring = $request->get('scoring') ?? '';
        $additional = $request->get('additional') ?? '';
        $changeNotes = $request->get('change_notes') ?? null;

        return [
            'title' => $request->get('title') ?? '',
            'setup' => (new ContentBuilder($setup))->getFullyHydratedContent(),
            'rules' => (new ContentBuilder($rules))->getFullyHydratedContent(),
            'scoring' => (new ContentBuilder($scoring))->getFullyHydratedContent(),
            'additional' => (new ContentBuilder($additional))->getFullyHydratedContent(),
            'change_notes' => $changeNotes ? (new ContentBuilder($changeNotes))->getFullyHydratedContent() : null,
        ];
    }

    public function view(Request $request, Strategy $strategy)
    {
        $strategy->loadMissing('newestVersion', 'publishedBy');

        return [
            'title' => $strategy->title,
            'suit' => $strategy->suit?->value,
            'suit_label' => $strategy->suit?->label(),
            'setup' => (new ContentBuilder($strategy->setup ?? ''))->getFullyHydratedContent(),
            'rules' => (new ContentBuilder($strategy->rules ?? ''))->getFullyHydratedContent(),
            'scoring' => (new ContentBuilder($strategy->scoring ?? ''))->getFullyHydratedContent(),
            'additional' => (new ContentBuilder($strategy->additional ?? ''))->getFullyHydratedContent(),
            'published_at' => $strategy->published_at?->format('m-d-Y'),
            'published_by' => $strategy->publishedBy?->name,
        ];
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Strategies/Index', [
            'strategies' => Strategy::with('approval', 'batch', 'season')
                ->orderBy('id', 'DESC')
                ->orderBy('title', 'ASC')
                ->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Strategies/StrategyForm', [
            'suit_options' => SuitEnum::toSelectOptions(),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
            'seasons' => SeasonListResource::collection(
                Season::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
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

    public function edit(Request $request, Strategy $strategy): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Strategies/StrategyForm', [
            'strategy' => $strategy->loadMissing('approval'),
            'suit_options' => SuitEnum::toSelectOptions(),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
            'seasons' => SeasonListResource::collection(
                Season::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
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
        $strategy = $this->validateAndSave($request);

        return to_route('admin.strategies.index')->withMessage($strategy->title.' created successfully!');
    }

    public function update(Request $request, Strategy $strategy): \Illuminate\Http\RedirectResponse
    {
        $strategy = $this->validateAndSave($request, $strategy);

        return to_route('admin.strategies.index')->withMessage($strategy->title.' updated successfully!');
    }

    public function delete(Request $request, Strategy $strategy): \Illuminate\Http\RedirectResponse
    {
        $name = $strategy->title;

        $strategy->delete();

        return to_route('admin.strategies.index')->withMessage($name.' has been deleted.');
    }

    public function publish(Request $request, Strategy $strategy): \Illuminate\Http\RedirectResponse
    {
        try {
            $strategy->publish($request->user());
        } catch (\Exception $exception) {
            return redirect()->back()->withMessage($exception->getMessage(), messageType: MessageTypeEnum::destructive);
        }

        return to_route('admin.strategies.index')->withMessage($strategy->title.' has been published!');
    }

    public function bulkApprove(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = Strategy::with('approval')->whereIn('id', $validated['ids'])->get();
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

        return redirect()->back()->withMessage("{$count} strategy(ies) approved.");
    }

    public function bulkPublish(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = Strategy::with('approval')->whereIn('id', $validated['ids'])->get();
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

        $message = "{$count} strategy(ies) published.";
        if (! empty($errors)) {
            $message .= ' Errors: '.implode('; ', $errors);
        }

        return redirect()->back()->withMessage($message);
    }

    public function bulkDelete(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $count = Strategy::whereIn('id', $validated['ids'])->whereNull('published_at')->delete();

        return redirect()->back()->withMessage("{$count} strategy(ies) deleted.");
    }

    private function validateAndSave(Request $request, ?Strategy $strategy = null): Strategy
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'season_id' => ['required', 'integer', 'exists:seasons,id'],
            'suit' => ['nullable', 'string', Rule::enum(SuitEnum::class)],
            'setup' => ['nullable', 'string'],
            'rules' => ['nullable', 'string'],
            'scoring' => ['nullable', 'string'],
            'additional' => ['nullable', 'string'],
            'front_image' => ['nullable', 'file', 'max:30000', 'mimes:heic,jpeg,jpg,png,webp'],
            'back_image' => ['nullable', 'file', 'max:30000', 'mimes:heic,jpeg,jpg,png,webp'],
            'combination_image' => ['nullable', 'file', 'max:30000', 'mimes:heic,jpeg,jpg,png,webp'],
            'internal_notes' => ['nullable', 'string'],
            'change_notes' => ['nullable', 'string'],
            'batch_id' => ['nullable', 'int', 'exists:batches,id'],
            'publish_directly' => ['required', 'boolean'],
            'approve_directly' => ['required', 'boolean'],
        ]);

        $existingImages = [
            'front_image' => $strategy?->front_image,
            'back_image' => $strategy?->back_image,
            'combination_image' => $strategy?->combination_image,
        ];

        $publishDirectly = $validated['publish_directly'];
        unset($validated['publish_directly']);
        $approveDirectly = $validated['approve_directly'];
        unset($validated['approve_directly']);
        $changeNotes = preg_replace("/(\r|\n)/", '', nl2br($validated['change_notes']));
        unset($validated['change_notes']);

        foreach (['setup', 'rules', 'scoring', 'additional'] as $field) {
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
                $filePath = "strategies/{$nameSlug}/{$fileName}";
                Storage::disk('public')->put($filePath, file_get_contents($validated[$imageField]));
                $validated[$imageField] = '/storage/'.$filePath;
            } else {
                unset($validated[$imageField]);
            }
        }

        if (! $strategy) {
            $strategy = Strategy::create($validated);
        } else {
            $strategy->loadMissing('approval');

            if (! $strategy->published_at) {
                $strategy->update($validated);
                $strategy->approval?->delete();
            } else {
                $validated['previous'] = $strategy->id;
                $validated['original'] = $strategy->original ?? $strategy->id;
                foreach ($existingImages as $key => $value) {
                    $validated[$key] = $validated[$key] ?? $value;
                }
                $strategy = Strategy::create($validated);
            }
        }

        CreateApprovalAction::handle(
            $strategy->refresh(),
            $request->user(),
            changeNotes: $changeNotes,
            approveDirectly: $approveDirectly
        );

        if ($publishDirectly) {
            self::publish($request, $strategy->refresh());
        }

        return $strategy;
    }
}
