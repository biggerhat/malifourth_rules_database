<?php

namespace App\Actions\Content;

use App\Models\Faq;
use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Database\Eloquent\Model;

class SyncContentReferencesAction
{
    public static function handle(Model $model): void
    {
        $fields = match (true) {
            $model instanceof Section => ['left_column', 'right_column'],
            $model instanceof Faq => ['answer'],
            default => ['content'],
        };

        $faqSlugs = [];
        $indexSlugs = [];
        $sectionSlugs = [];
        $pageSlugs = [];

        foreach ($fields as $field) {
            $content = $model->{$field} ?? '';
            if ($content === '') {
                continue;
            }

            $builder = new ContentBuilder($content);
            $tagSlugs = $builder->getTagSlugs();

            foreach ($tagSlugs as $tag => $slugs) {
                match ($tag) {
                    'faqLink' => $faqSlugs = array_merge($faqSlugs, $slugs),
                    'index', 'indexTooltip' => $indexSlugs = array_merge($indexSlugs, $slugs),
                    'section', 'sectionLink' => $sectionSlugs = array_merge($sectionSlugs, $slugs),
                    'pageLink' => $pageSlugs = array_merge($pageSlugs, $slugs),
                    default => null,
                };
            }
        }

        $faqIds = self::resolveIds(Faq::class, $faqSlugs);
        $indexIds = self::resolveIds(Index::class, $indexSlugs);
        $sectionIds = self::resolveIds(Section::class, $sectionSlugs);
        $pageIds = self::resolveIds(Page::class, $pageSlugs);

        $model->referencedFaqs()->sync($faqIds);
        $model->referencedIndices()->sync($indexIds);
        $model->referencedSections()->sync($sectionIds);
        $model->referencedPages()->sync($pageIds);
    }

    /**
     * Resolve slugs to the current (non-deleted) version IDs.
     *
     * Tags may reference old slugs belonging to soft-deleted versions.
     * We follow the `newest` pointer to get the live record's ID.
     */
    private static function resolveIds(string $modelClass, array $slugs): array
    {
        if (empty($slugs)) {
            return [];
        }

        return $modelClass::whereIn('slug', array_unique($slugs))
            ->withTrashed()
            ->get()
            ->map(fn ($model) => $model->newest ?? $model->id)
            ->unique()
            ->values()
            ->all();
    }
}
