<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->foreignId('previous')->nullable()->constrained('seasons', 'id')->after('batch_id');
            $table->foreignId('original')->nullable()->constrained('seasons', 'id')->after('previous');
            $table->foreignId('newest')->nullable()->index()->constrained('seasons', 'id')->after('original');
        });

        Schema::table('schemes', function (Blueprint $table) {
            $table->string('front_image')->nullable()->change();
        });

        Schema::table('strategies', function (Blueprint $table) {
            $table->string('front_image')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropForeign(['previous']);
            $table->dropForeign(['original']);
            $table->dropForeign(['newest']);
            $table->dropColumn(['previous', 'original', 'newest']);
        });

        Schema::table('schemes', function (Blueprint $table) {
            $table->string('front_image')->nullable(false)->change();
        });

        Schema::table('strategies', function (Blueprint $table) {
            $table->string('front_image')->nullable(false)->change();
        });
    }
};
