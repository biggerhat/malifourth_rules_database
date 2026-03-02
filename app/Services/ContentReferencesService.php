<?php

namespace App\Services;

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
use Illuminate\Database\Eloquent\Model;

class ContentReferencesService
{
    public static function getForModel(Model $model): array
    {
        $model->loadMissing([
            'newestVersion',
            'referencedFaqs',
            'referencedIndices',
            'referencedSections',
            'referencedPages',
            'referencedByFaqs',
            'referencedByIndices',
            'referencedBySections',
            'referencedByPages',
        ]);

        return [
            'references' => self::formatReferences($model),
            'referenced_by' => self::formatReferencedBy($model),
            'revision_history' => self::getRevisionHistory($model),
        ];
    }

    private static function formatReferences(Model $model): array
    {
        $items = [];

        foreach ($model->referencedPages as $page) {
            $items[] = self::formatItem($page, 'Page');
        }

        foreach ($model->referencedSections as $section) {
            $items[] = self::formatItem($section, 'Section');
        }

        foreach ($model->referencedIndices as $index) {
            $items[] = self::formatItem($index, 'Index');
        }

        foreach ($model->referencedFaqs as $faq) {
            $items[] = self::formatItem($faq, 'FAQ');
        }

        return $items;
    }

    private static function formatReferencedBy(Model $model): array
    {
        $items = [];

        foreach ($model->referencedByPages as $page) {
            $items[] = self::formatItem($page, 'Page');
        }

        foreach ($model->referencedBySections as $section) {
            $items[] = self::formatItem($section, 'Section');
        }

        foreach ($model->referencedByIndices as $index) {
            $items[] = self::formatItem($index, 'Index');
        }

        foreach ($model->referencedByFaqs as $faq) {
            $items[] = self::formatItem($faq, 'FAQ');
        }

        return $items;
    }

    private static function getRevisionHistory(Model $model): array
    {
        $originalId = $model->original ?? $model->id;

        $query = $model::withTrashed()
            ->where('id', $originalId)
            ->orWhere('original', $originalId)
            ->with('publishedBy', 'approval')
            ->orderByDesc('id');

        if ($model instanceof SeasonPage) {
            $query->with('season');
        }

        $versions = $query->get();

        $currentVersion = $model->newestVersion ?? $model;

        return $versions->map(fn ($version) => [
            'title' => ContentBuilder::parseTitleTags($version->title),
            'published_at' => $version->published_at?->format('m-d-Y'),
            'published_by' => $version->publishedBy?->name,
            'change_notes' => $version->approval?->change_notes
                ? strip_tags(preg_replace('/{{.*?}}/', '', $version->approval->change_notes))
                : null,
            'current' => $version->id === $currentVersion->id,
            'active' => $version->id === $model->id,
            'url' => self::getRevisionUrl($version, $currentVersion),
        ])->all();
    }

    private static function getRevisionUrl(Model $version, Model $currentVersion): string
    {
        $isCurrent = $version->id === $currentVersion->id;

        return match (true) {
            $version instanceof Faq => $isCurrent
                ? route('rules.faq.view', $currentVersion->slug)
                : route('rules.faq.history', $version->slug),
            $version instanceof Page => $isCurrent
                ? route('rules.page.view', $currentVersion->slug)
                : route('rules.page.history', $version->slug),
            $version instanceof Section => $isCurrent
                ? route('rules.section.view', $currentVersion->slug)
                : route('rules.section.history', $version->slug),
            $version instanceof Index => $isCurrent
                ? route('rules.index.view', $currentVersion->slug)
                : route('rules.index.history', $version->slug),
            $version instanceof Season => $isCurrent
                ? route('rules.gaining-grounds.season', $currentVersion->slug)
                : route('rules.gaining-grounds.season.history', $version->slug),
            $version instanceof Scheme => $isCurrent
                ? route('rules.gaining-grounds.scheme', $currentVersion->slug)
                : route('rules.gaining-grounds.scheme.history', $version->slug),
            $version instanceof SeasonPage => $isCurrent
                ? route('rules.gaining-grounds.season-page', [$currentVersion->season->slug, $currentVersion->slug])
                : route('rules.gaining-grounds.season-page.history', [$version->season->slug, $version->slug]),
            $version instanceof Strategy => $isCurrent
                ? route('rules.gaining-grounds.strategy', $currentVersion->slug)
                : route('rules.gaining-grounds.strategy.history', $version->slug),
            $version instanceof Errata => $isCurrent
                ? route('errata.view', $currentVersion->slug)
                : route('errata.history', $version->slug),
        };
    }

    private static function formatItem(Model $model, string $type): array
    {
        $route = match ($type) {
            'FAQ' => route('rules.faq.view', $model->slug),
            'Page' => route('rules.page.view', $model->slug),
            'Section' => route('rules.section.view', $model->slug),
            'Index' => route('rules.index.view', $model->slug),
            'Season' => route('rules.gaining-grounds.season', $model->slug),
            'Scheme' => route('rules.gaining-grounds.scheme', $model->slug),
            'Strategy' => route('rules.gaining-grounds.strategy', $model->slug),
            'Errata' => route('errata.view', $model->slug),
        };

        return [
            'title' => ContentBuilder::parseTitleTags($model->title),
            'type' => $type,
            'url' => $route,
        ];
    }
}
