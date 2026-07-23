<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('card_erratas', function (Blueprint $table) {
            $table->string('image')->nullable()->after('card_name');
        });

        // Carry forward the first usable image found among each card's entries
        // (in sort order) before the per-entry image columns are dropped.
        DB::table('card_erratas')->select('id')->orderBy('id')->each(function ($card) {
            $image = DB::table('card_errata_entries')
                ->where('card_errata_id', $card->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['front_image', 'back_image'])
                ->reduce(fn ($carry, $entry) => $carry ?? $entry->front_image ?? $entry->back_image, null);

            if ($image) {
                DB::table('card_erratas')->where('id', $card->id)->update(['image' => $image]);
            }
        });

        Schema::table('card_errata_entries', function (Blueprint $table) {
            $table->dropColumn(['front_image', 'back_image']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_errata_entries', function (Blueprint $table) {
            $table->string('front_image')->nullable();
            $table->string('back_image')->nullable();
        });

        // Best-effort, lossy reversal: put the card's image back on its first entry.
        DB::table('card_erratas')->whereNotNull('image')->select('id', 'image')->orderBy('id')->each(function ($card) {
            $firstEntryId = DB::table('card_errata_entries')
                ->where('card_errata_id', $card->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->value('id');

            if ($firstEntryId) {
                DB::table('card_errata_entries')->where('id', $firstEntryId)->update(['front_image' => $card->image]);
            }
        });

        Schema::table('card_erratas', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
