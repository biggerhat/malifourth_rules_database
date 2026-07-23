<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\FactionEnum;
use App\Enums\MessageTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\CardErrataListResource;
use App\Models\Batch;
use App\Models\CardErrata;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Str;

class CardErrataAdminController extends Controller
{
    private const IMAGE_RULES = ['nullable', 'file', 'max:30000', 'mimes:heic,jpeg,jpg,png,webp'];

    public function list(Request $request)
    {
        return CardErrataListResource::collection(
            CardErrata::orderBy('card_name', 'ASC')->orderBy('id', 'DESC')->get()
        )->toArray($request);
    }

    public function view(Request $request, CardErrata $cardErrata)
    {
        $cardErrata->loadMissing('newestVersion', 'publishedBy', 'entries');

        return [
            'faction' => $cardErrata->faction,
            'faction_label' => FactionEnum::from($cardErrata->faction)->label(),
            'card_name' => $cardErrata->card_name,
            'slug' => $cardErrata->slug,
            'image' => $cardErrata->image,
            'entries' => $cardErrata->entries->map(fn ($entry) => [
                'id' => $entry->id,
                'what_changed' => (new ContentBuilder($entry->what_changed ?? ''))->getFullyHydratedContent(),
                'what_it_was' => (new ContentBuilder($entry->what_it_was ?? ''))->getFullyHydratedContent(),
                'what_it_is_now' => (new ContentBuilder($entry->what_it_is_now ?? ''))->getFullyHydratedContent(),
            ]),
            'published_at' => $cardErrata->published_at?->format('m-d-Y'),
            'published_by' => $cardErrata->publishedBy?->name,
        ];
    }

    public function preview(Request $request)
    {
        $entries = $request->input('entries', []);

        return [
            'card_name' => $request->get('card_name') ?? '',
            'faction_label' => $request->get('faction') ? FactionEnum::tryFrom($request->get('faction'))?->label() : '',
            'entries' => collect($entries)->map(fn ($entry) => [
                'what_changed' => (new ContentBuilder($entry['what_changed'] ?? ''))->getFullyHydratedContent(),
                'what_it_was' => (new ContentBuilder($entry['what_it_was'] ?? ''))->getFullyHydratedContent(),
                'what_it_is_now' => (new ContentBuilder($entry['what_it_is_now'] ?? ''))->getFullyHydratedContent(),
            ])->values(),
        ];
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/CardErrata/Index', [
            'cardErratas' => CardErrata::with('approval', 'batch', 'entries')
                ->orderBy('id', 'DESC')
                ->orderBy('card_name', 'ASC')
                ->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/CardErrata/CardErrataForm', [
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
            'faction_options' => FactionEnum::toSelectOptions(),
        ]);
    }

    public function edit(Request $request, CardErrata $cardErrata): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/CardErrata/CardErrataForm', [
            'cardErrata' => $cardErrata->loadMissing('approval', 'entries'),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
            'faction_options' => FactionEnum::toSelectOptions(),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $cardErrata = $this->validateAndSave($request);

        return to_route('admin.card-errata.index')->withMessage($cardErrata->card_name.' created successfully!');
    }

    public function update(Request $request, CardErrata $cardErrata): \Illuminate\Http\RedirectResponse
    {
        $cardErrata = $this->validateAndSave($request, $cardErrata);

        return to_route('admin.card-errata.index')->withMessage($cardErrata->card_name.' updated successfully!');
    }

    public function delete(Request $request, CardErrata $cardErrata): \Illuminate\Http\RedirectResponse
    {
        $name = $cardErrata->card_name;

        $cardErrata->delete();

        return to_route('admin.card-errata.index')->withMessage($name.' has been deleted.');
    }

    public function publish(Request $request, CardErrata $cardErrata): \Illuminate\Http\RedirectResponse
    {
        try {
            $cardErrata->publish($request->user());
        } catch (\Exception $exception) {
            return redirect()->back()->withMessage($exception->getMessage(), messageType: MessageTypeEnum::destructive);
        }

        return to_route('admin.card-errata.index')->withMessage($cardErrata->card_name.' has been published!');
    }

    public function bulkApprove(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = CardErrata::with('approval')->whereIn('id', $validated['ids'])->get();
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

        return redirect()->back()->withMessage("{$count} card errata approved.");
    }

    public function bulkPublish(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = CardErrata::with('approval')->whereIn('id', $validated['ids'])->get();
        $count = 0;
        $errors = [];

        foreach ($items as $item) {
            try {
                $item->publish($request->user());
                $count++;
            } catch (\Exception $e) {
                $errors[] = $item->card_name.': '.$e->getMessage();
            }
        }

        $message = "{$count} card errata published.";
        if (! empty($errors)) {
            $message .= ' Errors: '.implode('; ', $errors);
        }

        return redirect()->back()->withMessage($message);
    }

    public function bulkDelete(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $count = CardErrata::whereIn('id', $validated['ids'])->whereNull('published_at')->delete();

        return redirect()->back()->withMessage("{$count} card errata deleted.");
    }

    private function validateAndSave(Request $request, ?CardErrata $cardErrata = null): CardErrata
    {
        $validated = $request->validate([
            'faction' => ['required', 'string', Rule::enum(FactionEnum::class)],
            'card_name' => ['required', 'string', 'max:255'],
            'internal_notes' => ['nullable', 'string'],
            'change_notes' => ['nullable', 'string'],
            'batch_id' => ['nullable', 'int', 'exists:batches,id'],
            'publish_directly' => ['required', 'boolean'],
            'approve_directly' => ['required', 'boolean'],
            'image' => self::IMAGE_RULES,
            'existing_image' => ['nullable', 'string'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.what_changed' => ['nullable', 'string'],
            'entries.*.what_it_was' => ['nullable', 'string'],
            'entries.*.what_it_is_now' => ['nullable', 'string'],
        ]);

        $existingImage = $cardErrata?->image;

        $publishDirectly = $validated['publish_directly'];
        $approveDirectly = $validated['approve_directly'];
        $changeNotes = $validated['change_notes'] ? preg_replace("/(\r|\n)/", '', nl2br($validated['change_notes'])) : null;
        $entries = $validated['entries'];

        $nameSlug = Str::slug($validated['card_name']);
        $image = $this->resolveImage($validated['image'] ?? null, $validated['existing_image'] ?? null, $nameSlug);

        $cardAttributes = [
            'faction' => $validated['faction'],
            'card_name' => $validated['card_name'],
            'image' => $image,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'batch_id' => $validated['batch_id'] ?? null,
        ];

        if (! $cardErrata) {
            $cardErrata = CardErrata::create($cardAttributes);
        } else {
            $cardErrata->loadMissing('approval');

            if (! $cardErrata->published_at) {
                $cardErrata->update($cardAttributes);
                $cardErrata->approval?->delete();
                $cardErrata->entries()->delete();
            } else {
                $cardAttributes['previous'] = $cardErrata->id;
                $cardAttributes['original'] = $cardErrata->original ?? $cardErrata->id;
                $cardAttributes['image'] = $cardAttributes['image'] ?? $existingImage;
                $cardErrata = CardErrata::create($cardAttributes);
            }
        }

        foreach ($entries as $index => $entry) {
            $cardErrata->entries()->create([
                'what_changed' => $entry['what_changed'] ?? null,
                'what_it_was' => $entry['what_it_was'] ?? null,
                'what_it_is_now' => $entry['what_it_is_now'] ?? null,
                'sort_order' => $index,
            ]);
        }

        $cardErrata->updateQuietly([
            'searchable_text' => $this->buildSearchableText($cardErrata),
        ]);

        CreateApprovalAction::handle(
            $cardErrata->refresh(),
            $request->user(),
            changeNotes: $changeNotes,
            approveDirectly: $approveDirectly
        );

        if ($publishDirectly) {
            self::publish($request, $cardErrata->refresh());
        }

        return $cardErrata;
    }

    private function resolveImage(mixed $file, ?string $existing, string $nameSlug): ?string
    {
        if ($file) {
            $extension = $file->extension();
            $uuid = Str::uuid();
            $fileName = sprintf('%s_%s.%s', $nameSlug, $uuid, $extension);
            $filePath = "card-errata/{$nameSlug}/{$fileName}";
            Storage::disk('public')->put($filePath, file_get_contents($file));

            return '/storage/'.$filePath;
        }

        return $existing;
    }

    private function buildSearchableText(CardErrata $cardErrata): string
    {
        $entryText = $cardErrata->entries()->get()->flatMap(fn ($entry) => [
            ContentBuilder::toSearchable($entry->what_changed ?? ''),
            ContentBuilder::toSearchable($entry->what_it_was ?? ''),
            ContentBuilder::toSearchable($entry->what_it_is_now ?? ''),
        ]);

        return collect([$cardErrata->card_name, $cardErrata->faction])
            ->concat($entryText)
            ->filter()
            ->implode(' ');
    }
}
