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
        Schema::create('strategies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->index();
            $table->string('suit')->nullable();
            $table->foreignId('season_id')->constrained('seasons', 'id')->cascadeOnDelete();
            $table->longText('setup')->nullable();
            $table->longText('rules')->nullable();
            $table->longText('scoring')->nullable();
            $table->longText('additional')->nullable();
            $table->longText('searchable_text')->nullable();
            $table->string('front_image');
            $table->string('back_image')->nullable();
            $table->string('combination_image')->nullable();
            $table->longText('internal_notes')->nullable();
            $table->foreignId('batch_id')->nullable()->constrained('batches', 'id');
            $table->foreignId('previous')->nullable()->constrained('strategies', 'id');
            $table->foreignId('original')->nullable()->constrained('strategies', 'id');
            $table->foreignId('newest')->nullable()->index()->constrained('strategies', 'id');
            $table->dateTime('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users', 'id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategies');
    }
};
