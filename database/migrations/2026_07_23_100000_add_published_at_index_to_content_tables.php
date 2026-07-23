<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Versioned content tables: filtered via whereNotNull('published_at')->whereNull('newest') everywhere.
    private const VERSIONED_TABLES = [
        'pages',
        'sections',
        'indices',
        'erratas',
        'faqs',
        'seasons',
        'season_pages',
        'schemes',
        'strategies',
    ];

    // Batches have no version chain (no `newest` column), just published_at.
    private const UNVERSIONED_TABLES = [
        'batches',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::VERSIONED_TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->index(['published_at', 'newest'], $table.'_published_at_newest_index');
            });
        }

        foreach (self::UNVERSIONED_TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->index('published_at', $table.'_published_at_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::VERSIONED_TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropIndex($table.'_published_at_newest_index');
            });
        }

        foreach (self::UNVERSIONED_TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropIndex($table.'_published_at_index');
            });
        }
    }
};
