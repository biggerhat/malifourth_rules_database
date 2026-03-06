<?php

namespace App\Console\Commands;

use App\Models\Batch;
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
use App\Services\ContentBuilder\ContentToTipTapConverter;
use Illuminate\Console\Command;

class MigrateContentToTipTap extends Command
{
    protected $signature = 'content:migrate-to-tiptap {--dry-run : Show what would be changed without saving}';

    protected $description = 'Migrate bracket-tag content to TipTap JSON format';

    private int $converted = 0;

    private int $skipped = 0;

    private int $errors = 0;

    private array $modelFields = [
        Section::class => ['left_column', 'right_column'],
        Faq::class => ['title', 'answer'],
        Scheme::class => ['prerequisites', 'reveal', 'scoring', 'additional'],
        Strategy::class => ['setup', 'rules', 'scoring', 'additional'],
        Page::class => ['content'],
        Index::class => ['content'],
        SeasonPage::class => ['content'],
        Season::class => ['content'],
        Errata::class => ['content'],
        Batch::class => ['release_notes'],
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN - no changes will be saved.');
        }

        foreach ($this->modelFields as $modelClass => $fields) {
            $this->migrateModel($modelClass, $fields, $dryRun);
        }

        $this->newLine();
        $this->info("Migration complete: {$this->converted} fields converted, {$this->skipped} skipped (already JSON or empty), {$this->errors} errors.");

        return self::SUCCESS;
    }

    private function migrateModel(string $modelClass, array $fields, bool $dryRun): void
    {
        $shortName = class_basename($modelClass);
        $this->info("Processing {$shortName}...");

        $query = $modelClass::withTrashed();
        $total = $query->count();
        $bar = $this->output->createProgressBar($total);

        $query->chunk(100, function ($models) use ($fields, $dryRun, $shortName, $bar) {
            foreach ($models as $model) {
                $changed = false;
                $hadError = false;

                foreach ($fields as $field) {
                    $content = $model->{$field} ?? '';

                    if ($content === '' || $content === null) {
                        $this->skipped++;

                        continue;
                    }

                    if (ContentBuilder::detectTipTapJson($content)) {
                        $this->skipped++;

                        continue;
                    }

                    try {
                        $converted = ContentToTipTapConverter::convert($content);
                        if (! $dryRun) {
                            $model->{$field} = $converted;
                            $changed = true;
                        } else {
                            $this->line("  Would convert {$shortName}#{$model->id}.{$field}");
                        }
                        $this->converted++;
                    } catch (\Throwable $e) {
                        $this->error("  Error converting {$shortName}#{$model->id}.{$field}: {$e->getMessage()}");
                        $this->errors++;
                        $hadError = true;
                    }
                }

                if ($changed && ! $dryRun && ! $hadError) {
                    $model->saveQuietly();
                } elseif ($hadError && $changed) {
                    $this->warn("  Skipping save for {$shortName}#{$model->id} due to conversion error.");
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
    }
}
