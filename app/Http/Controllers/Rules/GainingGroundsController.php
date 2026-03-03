<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;
use App\Models\Scheme;
use App\Models\Season;
use App\Models\SeasonPage;
use App\Models\Strategy;
use App\Services\ContentBuilder\ContentBuilder;
use App\Services\ContentReferencesService;
use Illuminate\Http\Request;

class GainingGroundsController extends Controller
{
    public function index(Request $request)
    {
        $seasons = Season::whereNotNull('published_at')
            ->whereNull('newest')
            ->orderByDesc('published_at')
            ->get();

        $latestSeason = $seasons->first();

        if (! $latestSeason) {
            return inertia('Rules/GainingGrounds/Index', [
                'seasons' => [],
                'season' => null,
                'strategies' => [],
                'schemes' => [],
                'seasonPages' => [],
                'references' => null,
            ]);
        }

        $seasonList = $seasons->map(fn (Season $season) => [
            'id' => $season->id,
            'title' => $season->title,
            'slug' => $season->slug,
        ]);

        $seasonPages = SeasonPage::where('season_id', $latestSeason->id)
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SeasonPage $page) => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'sort_order' => $page->sort_order,
            ]);

        if ($seasonPages->isNotEmpty()) {
            $firstPage = SeasonPage::where('season_id', $latestSeason->id)
                ->whereNotNull('published_at')
                ->whereNull('newest')
                ->orderBy('sort_order')
                ->first();
            $content = (new ContentBuilder($firstPage->content ?? ''))->getFullyHydratedContent();
        } else {
            $content = (new ContentBuilder($latestSeason->content ?? ''))->getFullyHydratedContent();
        }

        $strategies = Strategy::where('season_id', $latestSeason->id)
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->orderBy('title')
            ->get()
            ->map(fn (Strategy $strategy) => [
                'id' => $strategy->id,
                'title' => $strategy->title,
                'slug' => $strategy->slug,
                'suit' => $strategy->suit?->value,
                'suit_label' => $strategy->suit?->label(),
                'front_image' => $strategy->front_image,
            ]);

        $schemes = Scheme::where('season_id', $latestSeason->id)
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->orderBy('title')
            ->get()
            ->map(fn (Scheme $scheme) => [
                'id' => $scheme->id,
                'title' => $scheme->title,
                'slug' => $scheme->slug,
                'front_image' => $scheme->front_image,
            ]);

        return inertia('Rules/GainingGrounds/Index', [
            'seasons' => $seasonList,
            'season' => [
                'title' => $latestSeason->title,
                'slug' => $latestSeason->slug,
                'content' => $content,
                'url' => $latestSeason->url,
                'published_at' => $latestSeason->published_at->format('m-d-Y'),
                'published_by' => $latestSeason->publishedBy?->name,
            ],
            'strategies' => $strategies,
            'schemes' => $schemes,
            'seasonPages' => $seasonPages,
            'references' => ContentReferencesService::getForModel($latestSeason),
        ]);
    }

    public function viewSeason(Request $request, Season $season)
    {
        $season->loadMissing('newestVersion', 'publishedBy');
        $season = $season->newestVersion ?? $season;

        if (! $season->published_at) {
            return response('', 404);
        }

        $seasons = Season::whereNotNull('published_at')
            ->whereNull('newest')
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (Season $s) => [
                'id' => $s->id,
                'title' => $s->title,
                'slug' => $s->slug,
            ]);

        $seasonPages = SeasonPage::where('season_id', $season->id)
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SeasonPage $page) => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'sort_order' => $page->sort_order,
            ]);

        if ($seasonPages->isNotEmpty()) {
            $firstPage = SeasonPage::where('season_id', $season->id)
                ->whereNotNull('published_at')
                ->whereNull('newest')
                ->orderBy('sort_order')
                ->first();
            $content = (new ContentBuilder($firstPage->content ?? ''))->getFullyHydratedContent();
        } else {
            $content = (new ContentBuilder($season->content ?? ''))->getFullyHydratedContent();
        }

        $strategies = Strategy::where('season_id', $season->id)
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->orderBy('title')
            ->get()
            ->map(fn (Strategy $strategy) => [
                'id' => $strategy->id,
                'title' => $strategy->title,
                'slug' => $strategy->slug,
                'suit' => $strategy->suit?->value,
                'suit_label' => $strategy->suit?->label(),
                'front_image' => $strategy->front_image,
            ]);

        $schemes = Scheme::where('season_id', $season->id)
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->orderBy('title')
            ->get()
            ->map(fn (Scheme $scheme) => [
                'id' => $scheme->id,
                'title' => $scheme->title,
                'slug' => $scheme->slug,
                'front_image' => $scheme->front_image,
            ]);

        return inertia('Rules/GainingGrounds/SeasonView', [
            'seasons' => $seasons,
            'season' => [
                'title' => $season->title,
                'slug' => $season->slug,
                'content' => $content,
                'url' => $season->url,
                'published_at' => $season->published_at->format('m-d-Y'),
                'published_by' => $season->publishedBy?->name,
            ],
            'strategies' => $strategies,
            'schemes' => $schemes,
            'seasonPages' => $seasonPages,
            'references' => ContentReferencesService::getForModel($season),
        ]);
    }

    public function viewStrategy(Request $request, Strategy $strategy)
    {
        $strategy->loadMissing('newestVersion', 'publishedBy', 'season');
        $strategy = $strategy->newestVersion ?? $strategy;

        if (! $strategy->published_at) {
            return response('', 404);
        }

        return inertia('Rules/GainingGrounds/StrategyView', [
            'strategy' => [
                'title' => $strategy->title,
                'slug' => $strategy->slug,
                'suit' => $strategy->suit?->value,
                'suit_label' => $strategy->suit?->label(),
                'setup' => (new ContentBuilder($strategy->setup ?? ''))->getFullyHydratedContent(),
                'rules' => (new ContentBuilder($strategy->rules ?? ''))->getFullyHydratedContent(),
                'scoring' => (new ContentBuilder($strategy->scoring ?? ''))->getFullyHydratedContent(),
                'additional' => (new ContentBuilder($strategy->additional ?? ''))->getFullyHydratedContent(),
                'front_image' => $strategy->front_image,
                'back_image' => $strategy->back_image,
                'combination_image' => $strategy->combination_image,
                'published_at' => $strategy->published_at->format('m-d-Y'),
                'published_by' => $strategy->publishedBy?->name,
            ],
            'season' => [
                'title' => $strategy->season?->title,
                'slug' => $strategy->season?->slug,
            ],
            'references' => ContentReferencesService::getForModel($strategy),
        ]);
    }

    public function viewScheme(Request $request, Scheme $scheme)
    {
        $scheme->loadMissing('newestVersion', 'publishedBy', 'season', 'nextScheme1', 'nextScheme2', 'nextScheme3');
        $scheme = $scheme->newestVersion ?? $scheme;

        if (! $scheme->published_at) {
            return response('', 404);
        }

        $nextSchemes = collect([$scheme->nextScheme1, $scheme->nextScheme2, $scheme->nextScheme3])
            ->filter()
            ->map(fn (Scheme $next) => [
                'id' => $next->id,
                'title' => $next->title,
                'slug' => $next->slug,
                'front_image' => $next->front_image,
            ])
            ->values();

        return inertia('Rules/GainingGrounds/SchemeView', [
            'scheme' => [
                'title' => $scheme->title,
                'slug' => $scheme->slug,
                'prerequisites' => (new ContentBuilder($scheme->prerequisites ?? ''))->getFullyHydratedContent(),
                'reveal' => (new ContentBuilder($scheme->reveal ?? ''))->getFullyHydratedContent(),
                'scoring' => (new ContentBuilder($scheme->scoring ?? ''))->getFullyHydratedContent(),
                'additional' => (new ContentBuilder($scheme->additional ?? ''))->getFullyHydratedContent(),
                'front_image' => $scheme->front_image,
                'back_image' => $scheme->back_image,
                'combination_image' => $scheme->combination_image,
                'published_at' => $scheme->published_at->format('m-d-Y'),
                'published_by' => $scheme->publishedBy?->name,
            ],
            'season' => [
                'title' => $scheme->season?->title,
                'slug' => $scheme->season?->slug,
            ],
            'next_schemes' => $nextSchemes,
            'references' => ContentReferencesService::getForModel($scheme),
        ]);
    }

    public function viewSeasonHistory(Request $request, Season $season)
    {
        $season->loadMissing('newestVersion', 'publishedBy');
        $currentVersion = $season->newestVersion ?? $season;

        if ($currentVersion->id === $season->id) {
            return redirect()->route('rules.gaining-grounds.season', $season->slug);
        }

        if (! $season->published_at) {
            return response('', 404);
        }

        $content = (new ContentBuilder($season->content ?? ''))->getFullyHydratedContent();

        return inertia('Rules/GainingGrounds/SeasonView', [
            'seasons' => [],
            'season' => [
                'title' => $season->title,
                'slug' => $season->slug,
                'content' => $content,
                'url' => $season->url,
                'published_at' => $season->published_at->format('m-d-Y'),
                'published_by' => $season->publishedBy?->name,
            ],
            'strategies' => [],
            'schemes' => [],
            'references' => ContentReferencesService::getForModel($season),
            'viewing_old_version' => true,
            'current_version_url' => route('rules.gaining-grounds.season', $currentVersion->slug),
        ]);
    }

    public function viewStrategyHistory(Request $request, Strategy $strategy)
    {
        $strategy->loadMissing('newestVersion', 'publishedBy', 'season');
        $currentVersion = $strategy->newestVersion ?? $strategy;

        if ($currentVersion->id === $strategy->id) {
            return redirect()->route('rules.gaining-grounds.strategy', $strategy->slug);
        }

        if (! $strategy->published_at) {
            return response('', 404);
        }

        return inertia('Rules/GainingGrounds/StrategyView', [
            'strategy' => [
                'title' => $strategy->title,
                'slug' => $strategy->slug,
                'suit' => $strategy->suit?->value,
                'suit_label' => $strategy->suit?->label(),
                'setup' => (new ContentBuilder($strategy->setup ?? ''))->getFullyHydratedContent(),
                'rules' => (new ContentBuilder($strategy->rules ?? ''))->getFullyHydratedContent(),
                'scoring' => (new ContentBuilder($strategy->scoring ?? ''))->getFullyHydratedContent(),
                'additional' => (new ContentBuilder($strategy->additional ?? ''))->getFullyHydratedContent(),
                'front_image' => $strategy->front_image,
                'back_image' => $strategy->back_image,
                'combination_image' => $strategy->combination_image,
                'published_at' => $strategy->published_at->format('m-d-Y'),
                'published_by' => $strategy->publishedBy?->name,
            ],
            'season' => [
                'title' => $strategy->season?->title,
                'slug' => $strategy->season?->slug,
            ],
            'references' => ContentReferencesService::getForModel($strategy),
            'viewing_old_version' => true,
            'current_version_url' => route('rules.gaining-grounds.strategy', $currentVersion->slug),
        ]);
    }

    public function viewSeasonPage(Request $request, Season $season, SeasonPage $seasonPage)
    {
        $seasonPage->loadMissing('newestVersion', 'publishedBy', 'season');
        $seasonPage = $seasonPage->newestVersion ?? $seasonPage;

        if (! $seasonPage->published_at) {
            return response('', 404);
        }

        $seasonPages = SeasonPage::where('season_id', $seasonPage->season_id)
            ->whereNotNull('published_at')
            ->whereNull('newest')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SeasonPage $page) => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'sort_order' => $page->sort_order,
            ]);

        return inertia('Rules/GainingGrounds/SeasonPageView', [
            'seasonPage' => [
                'title' => $seasonPage->title,
                'slug' => $seasonPage->slug,
                'content' => (new ContentBuilder($seasonPage->content ?? ''))->getFullyHydratedContent(),
                'published_at' => $seasonPage->published_at->format('m-d-Y'),
                'published_by' => $seasonPage->publishedBy?->name,
            ],
            'season' => [
                'title' => $seasonPage->season?->title,
                'slug' => $seasonPage->season?->slug,
            ],
            'seasonPages' => $seasonPages,
            'references' => ContentReferencesService::getForModel($seasonPage),
        ]);
    }

    public function viewSeasonPageHistory(Request $request, Season $season, SeasonPage $seasonPage)
    {
        $seasonPage->loadMissing('newestVersion', 'publishedBy', 'season');
        $currentVersion = $seasonPage->newestVersion ?? $seasonPage;

        if ($currentVersion->id === $seasonPage->id) {
            return redirect()->route('rules.gaining-grounds.season-page', [$seasonPage->season->slug, $seasonPage->slug]);
        }

        if (! $seasonPage->published_at) {
            return response('', 404);
        }

        return inertia('Rules/GainingGrounds/SeasonPageView', [
            'seasonPage' => [
                'title' => $seasonPage->title,
                'slug' => $seasonPage->slug,
                'content' => (new ContentBuilder($seasonPage->content ?? ''))->getFullyHydratedContent(),
                'published_at' => $seasonPage->published_at->format('m-d-Y'),
                'published_by' => $seasonPage->publishedBy?->name,
            ],
            'season' => [
                'title' => $seasonPage->season?->title,
                'slug' => $seasonPage->season?->slug,
            ],
            'seasonPages' => [],
            'references' => ContentReferencesService::getForModel($seasonPage),
            'viewing_old_version' => true,
            'current_version_url' => route('rules.gaining-grounds.season-page', [$currentVersion->season->slug, $currentVersion->slug]),
        ]);
    }

    public function viewSchemeHistory(Request $request, Scheme $scheme)
    {
        $scheme->loadMissing('newestVersion', 'publishedBy', 'season');
        $currentVersion = $scheme->newestVersion ?? $scheme;

        if ($currentVersion->id === $scheme->id) {
            return redirect()->route('rules.gaining-grounds.scheme', $scheme->slug);
        }

        if (! $scheme->published_at) {
            return response('', 404);
        }

        return inertia('Rules/GainingGrounds/SchemeView', [
            'scheme' => [
                'title' => $scheme->title,
                'slug' => $scheme->slug,
                'prerequisites' => (new ContentBuilder($scheme->prerequisites ?? ''))->getFullyHydratedContent(),
                'reveal' => (new ContentBuilder($scheme->reveal ?? ''))->getFullyHydratedContent(),
                'scoring' => (new ContentBuilder($scheme->scoring ?? ''))->getFullyHydratedContent(),
                'additional' => (new ContentBuilder($scheme->additional ?? ''))->getFullyHydratedContent(),
                'front_image' => $scheme->front_image,
                'back_image' => $scheme->back_image,
                'combination_image' => $scheme->combination_image,
                'published_at' => $scheme->published_at->format('m-d-Y'),
                'published_by' => $scheme->publishedBy?->name,
            ],
            'season' => [
                'title' => $scheme->season?->title,
                'slug' => $scheme->season?->slug,
            ],
            'next_schemes' => [],
            'references' => ContentReferencesService::getForModel($scheme),
            'viewing_old_version' => true,
            'current_version_url' => route('rules.gaining-grounds.scheme', $currentVersion->slug),
        ]);
    }
}
