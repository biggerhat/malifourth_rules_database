<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\CardErrata;
use App\Models\Errata;
use App\Models\Faq;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ChangelogController extends Controller
{
    private const PER_PAGE = 20;

    private const FEED_LIMIT = 50;

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
            ['path' => route('changelog.index'), 'pageName' => 'page'],
        );

        $items = collect($paginator->items())->map(fn (array $entry) => [
            ...$entry,
            'published_at' => $entry['published_at']->format('m-d-Y'),
        ])->values();

        return inertia('Rules/Changelog', [
            'entries' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function feed()
    {
        $entries = $this->collectEntries()->take(self::FEED_LIMIT);

        $xml = view('feeds.changelog', [
            'entries' => $entries,
            'feedUrl' => route('changelog.feed'),
            'siteUrl' => route('changelog.index'),
        ])->render();

        return response($xml, 200)->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    private function collectEntries(): Collection
    {
        $errata = Errata::published()->whereNull('newest')->with('approval')->get()
            ->map(fn (Errata $errata) => [
                'type' => 'errata',
                'type_label' => 'Errata',
                'title' => ContentBuilder::toPlainText($errata->title),
                'excerpt' => $this->excerpt($errata->approval?->change_notes ?: $errata->content),
                'url' => route('errata.view', $errata->slug),
                'published_at' => $errata->published_at,
            ]);

        $faqs = Faq::published()->whereNull('newest')->with('approval')->get()
            ->map(fn (Faq $faq) => [
                'type' => 'faq',
                'type_label' => 'FAQ',
                'title' => ContentBuilder::toPlainText($faq->title),
                'excerpt' => $this->excerpt($faq->approval?->change_notes ?: $faq->answer),
                'url' => route('rules.faq.view', $faq->slug),
                'published_at' => $faq->published_at,
            ]);

        $cardErrata = CardErrata::published()->whereNull('newest')->with(['approval', 'entries'])->get()
            ->map(function (CardErrata $cardErrata) {
                $changeNotes = $cardErrata->approval?->change_notes
                    ?: $cardErrata->entries->pluck('what_changed')->filter()->implode(' ');

                return [
                    'type' => 'card_errata',
                    'type_label' => 'Card Errata',
                    'title' => $cardErrata->title,
                    'excerpt' => $this->excerpt($changeNotes),
                    'url' => route('errata.cards.view', $cardErrata->slug),
                    'published_at' => $cardErrata->published_at,
                ];
            });

        return $errata->concat($faqs)->concat($cardErrata)
            ->sortByDesc(fn (array $entry) => $entry['published_at'])
            ->values();
    }

    private function excerpt(?string $text): ?string
    {
        if (! $text) {
            return null;
        }

        return Str::limit(ContentBuilder::toPlainText($text), 200);
    }
}
