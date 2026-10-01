<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // The nine content tables searched by Rules\SearchController and API\V1\SearchController.
    private const SEARCHABLE_TABLES = [
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

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // FULLTEXT indexes are a MySQL/PostgreSQL feature; sqlite (used in tests) falls
        // back to LIKE search instead, so there's nothing to index there.
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::SEARCHABLE_TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->fullText(['title', 'searchable_text'], $table.'_title_searchable_text_fulltext');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::SEARCHABLE_TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropFullText($table.'_title_searchable_text_fulltext');
            });
        }
    }
};
