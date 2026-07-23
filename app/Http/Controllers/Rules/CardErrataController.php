<?php

namespace App\Http\Controllers\Rules;

use App\Enums\FactionEnum;
use App\Http\Controllers\Controller;
use App\Models\CardErrata;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;

class CardErrataController extends Controller
{
    public function index(Request $request)
    {
        $cards = CardErrata::published()
            ->whereNull('newest')
            ->with('entries')
            ->orderBy('card_name')
            ->get()
            ->map(fn (CardErrata $card) => [
                'faction' => $card->faction,
                'card_name' => $card->card_name,
                'slug' => $card->slug,
                'entry_summaries' => $card->entries
                    ->map(fn ($entry) => ContentBuilder::toPlainText($entry->what_changed ?? ''))
                    ->filter()
                    ->values(),
            ])
            ->groupBy('faction');

        $factions = collect(FactionEnum::cases())
            ->map(fn (FactionEnum $faction) => [
                'value' => $faction->value,
                'label' => $faction->label(),
                'cards' => $cards->get($faction->value, collect())->values(),
            ])
            ->filter(fn (array $group) => $group['cards']->isNotEmpty())
            ->values();

        return inertia('Errata/CardErrata/Index', [
            'factions' => $factions,
        ]);
    }

    public function view(Request $request, CardErrata $cardErrata)
    {
        $cardErrata->loadMissing('newestVersion', 'publishedBy');
        $cardErrata = $cardErrata->newestVersion ?? $cardErrata;

        if (! $cardErrata->published_at) {
            return response('', 404);
        }

        return $this->renderCard($cardErrata);
    }

    public function viewHistory(Request $request, CardErrata $cardErrata)
    {
        $cardErrata->loadMissing('newestVersion', 'publishedBy');

        $currentVersion = $cardErrata->newestVersion ?? $cardErrata;

        if ($currentVersion->id === $cardErrata->id) {
            return redirect()->route('errata.cards.view', $cardErrata->slug);
        }

        if (! $cardErrata->published_at) {
            return response('', 404);
        }

        return $this->renderCard($cardErrata, [
            'viewing_old_version' => true,
            'current_version_url' => route('errata.cards.view', $currentVersion->slug),
        ]);
    }

    private function renderCard(CardErrata $cardErrata, array $extra = [])
    {
        $cardErrata->loadMissing('entries');

        return inertia('Errata/CardErrata/CardErrataView', array_merge([
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
        ], $extra));
    }
}
