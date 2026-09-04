<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\CardErrata;
use App\Models\Errata;
use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Scheme;
use App\Models\Season;
use App\Models\SeasonPage;
use App\Models\Section;
use App\Models\Strategy;
use App\Services\ContentBuilder\ContentBuilder;
use App\Services\ContentReferencesService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Public, read-only log of who approved and published each currently-live
 * piece of content, for auditability. Distinct from the Changelog, which
 * surfaces *what* changed (recency + change notes) for Errata/FAQ/Card
 * Errata specifically; this page surfaces *who* is accountable, across
 * every approvable content type.
 */
class TransparencyController extends Controller
{
    private const PER_PAGE = 25;

    /**
     * @var array<int, array{model: class-string<Model>, label: string}>
     */
    private const TYPES = [
        ['model' => Page::class, 'label' => 'Page'],
        ['model' => Section::class, 'label' => 'Section'],
        ['model' => Index::class, 'label' => 'Index'],
        ['model' => Faq::class, 'label' => 'FAQ'],
        ['model' => Errata::class, 'label' => 'Errata'],
        ['model' => Season::class, 'label' => 'Season'],
        ['model' => SeasonPage::class, 'label' => 'Season Page'],
        ['model' => Strategy::class, 'label' => 'Strategy'],
        ['model' => Scheme::class, 'label' => 'Scheme'],
    ];

    public function index(Request $request)
    {
        $entries = $this->collectEntries();
        $page = (int) $request->query('page', 1);
        $slice = $entries->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

        $paginator = new LengthAwarePaginator(
            $slice,
            $entries->count(),
            self::PER_PAGE,
            $page,
            ['path' => route('transparency.index'), 'pageName' => 'page'],
        );

        $items = collect($paginator->items())->map(fn (array $entry) => [
            ...$entry,
            'published_at' => $entry['published_at']->format('m-d-Y'),
        ])->values();

        return inertia('Rules/Transparency', [
            'entries' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectEntries(): Collection
    {
        $entries = collect();

        foreach (self::TYPES as $type) {
            $model = $type['model'];

            $query = $model::query()
                ->published()
                ->whereNull('newest')
                ->with(['approval.approvedBy', 'publishedBy']);

            if ($model === SeasonPage::class) {
                $query->with('season');
            }

            $entries = $entries->concat(
                $query->get()->map(
                    fn (Model $row) => $this->formatEntry(
                        $row,
                        $type['label'],
                        fn () => ContentReferencesService::getRevisionUrl($row, $row),
                    )
                )
            );
        }

        $cardErrata = CardErrata::query()
            ->published()
            ->whereNull('newest')
            ->with(['approval.approvedBy', 'publishedBy'])
            ->get()
            ->map(fn (CardErrata $row) => $this->formatEntry(
                $row,
                'Card Errata',
                fn () => route('errata.cards.view', $row->slug),
            ));

        return $entries->concat($cardErrata)
            ->sortByDesc(fn (array $entry) => $entry['published_at'])
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatEntry(Model $model, string $typeLabel, Closure $url): array
    {
        return [
            'type' => Str::slug($typeLabel, '_'),
            'type_label' => $typeLabel,
            'title' => ContentBuilder::toPlainText((string) ($model->title ?? '')),
            'url' => $url(),
            'published_at' => $model->published_at,
            'published_by' => $model->publishedBy?->name,
            'approved_by' => $model->approval?->approvedBy?->name,
            'change_notes' => $this->publicChangeNotes($model->approval?->change_notes),
        ];
    }

    /**
     * Only ever reads `change_notes` (author-facing summary of the edit).
     * `internal_notes` on the Approval model is explicitly internal-only
     * and must never be surfaced here.
     */
    private function publicChangeNotes(?string $notes): ?string
    {
        if (! $notes) {
            return null;
        }

        return Str::limit(ContentBuilder::toPlainText($notes), 200);
    }
}
