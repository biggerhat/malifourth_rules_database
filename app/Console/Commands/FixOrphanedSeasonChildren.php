<?php

namespace App\Console\Commands;

use App\Models\Scheme;
use App\Models\Season;
use App\Models\SeasonPage;
use App\Models\Strategy;
use Illuminate\Console\Command;

class FixOrphanedSeasonChildren extends Command
{
    protected $signature = 'seasons:fix-orphaned {--dry-run : Show what would be changed without saving}';

    protected $description = 'Re-point season pages, strategies, and schemes from soft-deleted seasons to their newest version';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $deletedSeasons = Season::onlyTrashed()->whereNotNull('newest')->get();

        if ($deletedSeasons->isEmpty()) {
            $this->info('No soft-deleted seasons with a newest version found.');

            return self::SUCCESS;
        }

        $totalFixed = 0;

        foreach ($deletedSeasons as $season) {
            $newestId = $season->newest;
            $newest = Season::find($newestId);

            if (! $newest) {
                $this->warn("Season #{$season->id} '{$season->title}' points to newest #{$newestId} which doesn't exist, skipping.");

                continue;
            }

            foreach ([SeasonPage::class, Strategy::class, Scheme::class] as $model) {
                $count = $model::where('season_id', $season->id)->count();
                if ($count > 0) {
                    $label = class_basename($model);
                    if ($dryRun) {
                        $this->line("  Would move {$count} {$label}(s) from Season #{$season->id} to #{$newestId}");
                    } else {
                        $model::where('season_id', $season->id)->update(['season_id' => $newestId]);
                        $this->line("  Moved {$count} {$label}(s) from Season #{$season->id} to #{$newestId}");
                    }
                    $totalFixed += $count;
                }
            }
        }

        $action = $dryRun ? 'Would fix' : 'Fixed';
        $this->info("{$action} {$totalFixed} orphaned record(s).");

        return self::SUCCESS;
    }
}
