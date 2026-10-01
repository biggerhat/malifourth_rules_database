<?php

namespace App\Http\Controllers;

use App\Models\Batch;
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
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap-xml', 3600, fn () => $this->buildXml());

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    private function buildXml(): string
    {
        $urls = collect([
            ['loc' => route('index'), 'lastmod' => null, 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('rules.index'), 'lastmod' => null, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => route('rules.faq.index'), 'lastmod' => null, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => route('errata.index'), 'lastmod' => null, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => route('errata.cards.index'), 'lastmod' => null, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => route('rules.gaining-grounds.index'), 'lastmod' => null, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => route('changelog.index'), 'lastmod' => null, 'changefreq' => 'daily', 'priority' => '0.6'],
            ['loc' => route('transparency.index'), 'lastmod' => null, 'changefreq' => 'daily', 'priority' => '0.4'],
        ]);

        $urls = $urls->concat(
            Page::published()->whereNull('newest')->orderBy('page_number')->get()
                ->map(fn (Page $page) => [
                    'loc' => route('rules.page.view', $page->slug),
                    'lastmod' => $page->published_at ?? $page->updated_at,
                    'changefreq' => 'monthly',
                    'priority' => '0.9',
                ])
        );

        $urls = $urls->concat(
            Section::published()->whereNull('newest')->get()
                ->map(fn (Section $section) => [
                    'loc' => route('rules.section.view', $section->slug),
                    'lastmod' => $section->published_at ?? $section->updated_at,
                    'changefreq' => 'monthly',
                    'priority' => '0.8',
                ])
        );

        $urls = $urls->concat(
            Index::published()->whereNull('newest')->get()
                ->map(fn (Index $index) => [
                    'loc' => route('rules.index.view', $index->slug),
                    'lastmod' => $index->published_at ?? $index->updated_at,
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ])
        );

        $urls = $urls->concat(
            Faq::published()->whereNull('newest')->get()
                ->map(fn (Faq $faq) => [
                    'loc' => route('rules.faq.view', $faq->slug),
                    'lastmod' => $faq->published_at ?? $faq->updated_at,
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ])
        );

        $urls = $urls->concat(
            Errata::published()->whereNull('newest')->get()
                ->map(fn (Errata $errata) => [
                    'loc' => route('errata.view', $errata->slug),
                    'lastmod' => $errata->published_at ?? $errata->updated_at,
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ])
        );

        $urls = $urls->concat(
            CardErrata::published()->whereNull('newest')->get()
                ->map(fn (CardErrata $cardErrata) => [
                    'loc' => route('errata.cards.view', $cardErrata->slug),
                    'lastmod' => $cardErrata->published_at ?? $cardErrata->updated_at,
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ])
        );

        $urls = $urls->concat(
            Batch::where('is_public', true)->whereNotNull('published_at')->get()
                ->map(fn (Batch $batch) => [
                    'loc' => route('errata.batch', $batch->slug),
                    'lastmod' => $batch->published_at ?? $batch->updated_at,
                    'changefreq' => 'yearly',
                    'priority' => '0.5',
                ])
        );

        $urls = $urls->concat(
            Season::published()->whereNull('newest')->get()
                ->map(fn (Season $season) => [
                    'loc' => route('rules.gaining-grounds.season', $season->slug),
                    'lastmod' => $season->published_at ?? $season->updated_at,
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ])
        );

        $urls = $urls->concat(
            Strategy::published()->whereNull('newest')->get()
                ->map(fn (Strategy $strategy) => [
                    'loc' => route('rules.gaining-grounds.strategy', $strategy->slug),
                    'lastmod' => $strategy->published_at ?? $strategy->updated_at,
                    'changefreq' => 'yearly',
                    'priority' => '0.6',
                ])
        );

        $urls = $urls->concat(
            Scheme::published()->whereNull('newest')->get()
                ->map(fn (Scheme $scheme) => [
                    'loc' => route('rules.gaining-grounds.scheme', $scheme->slug),
                    'lastmod' => $scheme->published_at ?? $scheme->updated_at,
                    'changefreq' => 'yearly',
                    'priority' => '0.6',
                ])
        );

        $urls = $urls->concat(
            SeasonPage::published()->whereNull('newest')->with('season')->get()
                ->filter(fn (SeasonPage $seasonPage) => $seasonPage->season !== null)
                ->map(fn (SeasonPage $seasonPage) => [
                    'loc' => route('rules.gaining-grounds.season-page', [$seasonPage->season->slug, $seasonPage->slug]),
                    'lastmod' => $seasonPage->published_at ?? $seasonPage->updated_at,
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ])
        );

        $entries = $urls->map(function (array $url) {
            $loc = htmlspecialchars($url['loc'], ENT_QUOTES | ENT_XML1, 'UTF-8');
            $lastmod = $url['lastmod'] ? '<lastmod>'.$url['lastmod']->toAtomString().'</lastmod>' : '';
            $changefreq = "<changefreq>{$url['changefreq']}</changefreq>";
            $priority = "<priority>{$url['priority']}</priority>";

            return "<url><loc>{$loc}</loc>{$lastmod}{$changefreq}{$priority}</url>";
        })->implode('');

        return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$entries.'</urlset>';
    }
}
