<?php

namespace App\Console\Commands;

use App\Actions\Content\SyncContentReferencesAction;
use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Console\Command;

class SyncContentReferencesCommand extends Command
{
    protected $signature = 'content:sync-references {--debug : Show detailed output per model}';

    protected $description = 'Backfill content reference pivot tables for all Pages, Sections, and Indices';

    public function handle(): int
    {
        $debug = $this->option('debug');

        $models = [
            'Pages' => Page::class,
            'Sections' => Section::class,
            'Indices' => Index::class,
        ];

        foreach ($models as $label => $modelClass) {
            $query = $modelClass::withTrashed();
            $count = $query->count();

            $this->info("Syncing {$count} {$label}...");

            if (! $debug) {
                $bar = $this->output->createProgressBar($count);
            }

            $query->each(function ($model) use ($debug, &$bar) {
                if ($debug) {
                    $this->debugModel($model);
                }

                SyncContentReferencesAction::handle($model);

                if (! $debug) {
                    $bar->advance();
                }
            });

            if (! $debug) {
                $bar->finish();
                $this->newLine();
            }
        }

        $this->info('Content references synced successfully.');

        return self::SUCCESS;
    }

    private function debugModel($model): void
    {
        $fields = match (true) {
            $model instanceof Section => ['left_column', 'right_column'],
            default => ['content'],
        };

        $allSlugs = [];
        foreach ($fields as $field) {
            $content = $model->{$field} ?? '';
            if ($content === '') {
                continue;
            }

            $builder = new ContentBuilder($content);
            $slugs = $builder->getTagSlugs();
            if (! empty($slugs)) {
                $allSlugs = array_merge($allSlugs, $slugs);
            }
        }

        $class = class_basename($model);
        if (empty($allSlugs)) {
            $this->line("  [{$class} #{$model->id}] {$model->title} — no tags found");
        } else {
            $this->info("  [{$class} #{$model->id}] {$model->title}");
            foreach ($allSlugs as $tag => $slugs) {
                $this->line("    {$tag}: ".implode(', ', $slugs));
            }
        }
    }
}
