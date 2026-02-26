<?php

namespace App\Services;

use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Database\Eloquent\Model;

class ContentReferencesService
{
    public static function getForModel(Model $model): array
    {
        $model->loadMissing([
            'newestVersion',
            'referencedIndices',
            'referencedSections',
            'referencedPages',
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

        return $items;
    }

    private static function getRevisionHistory(Model $model): array
    {
        $originalId = $model->original ?? $model->id;

        $versions = $model::withTrashed()
            ->where('id', $originalId)
            ->orWhere('original', $originalId)
            ->with('publishedBy', 'approval')
            ->orderByDesc('id')
            ->get();

        $currentVersion = $model->newestVersion ?? $model;

        return $versions->map(fn ($version) => [
            'title' => ContentBuilder::parseTitleTags($version->title),
            'published_at' => $version->published_at?->format('m-d-Y'),
            'published_by' => $version->publishedBy?->name,
            'change_notes' => $version->approval?->change_notes
                ? strip_tags(preg_replace('/{{.*?}}/', '', $version->approval->change_notes))
                : null,
            'current' => $version->id === $currentVersion->id,
            'url' => self::getRevisionUrl($version, $currentVersion),
        ])->all();
    }

    private static function getRevisionUrl(Model $version, Model $currentVersion): string
    {
        $isCurrent = $version->id === $currentVersion->id;

        return match (true) {
            $version instanceof Page => $isCurrent
                ? route('rules.page.view', $currentVersion->slug)
                : route('rules.page.history', $version->slug),
            $version instanceof Section => $isCurrent
                ? route('rules.section.view', $currentVersion->slug)
                : route('rules.section.history', $version->slug),
            $version instanceof Index => $isCurrent
                ? route('rules.index.view', $currentVersion->slug)
                : route('rules.index.history', $version->slug),
        };
    }

    private static function formatItem(Model $model, string $type): array
    {
        $route = match ($type) {
            'Page' => route('rules.page.view', $model->slug),
            'Section' => route('rules.section.view', $model->slug),
            'Index' => route('rules.index.view', $model->slug),
        };

        return [
            'title' => ContentBuilder::parseTitleTags($model->title),
            'type' => $type,
            'url' => $route,
        ];
    }
}
