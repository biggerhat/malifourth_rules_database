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
            $table->string('front_image')->nullable()->after('card_name');
            $table->string('back_image')->nullable()->after('front_image');
        });

        // The single consolidated image becomes the front face; back is left
        // for admins to add via the new dual-image upload.
        DB::table('card_erratas')->whereNotNull('image')->select('id', 'image')->orderBy('id')->each(function ($card) {
            DB::table('card_erratas')->where('id', $card->id)->update(['front_image' => $card->image]);
        });

        Schema::table('card_erratas', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_erratas', function (Blueprint $table) {
            $table->string('image')->nullable()->after('card_name');
        });

        DB::table('card_erratas')->whereNotNull('front_image')->select('id', 'front_image')->orderBy('id')->each(function ($card) {
            DB::table('card_erratas')->where('id', $card->id)->update(['image' => $card->front_image]);
        });

        Schema::table('card_erratas', function (Blueprint $table) {
            $table->dropColumn(['front_image', 'back_image']);
        });
    }
};
