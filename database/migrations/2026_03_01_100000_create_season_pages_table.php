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
        Schema::create('season_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->index();
            $table->foreignId('season_id')->constrained('seasons', 'id')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->longText('content')->nullable();
            $table->longText('searchable_text')->nullable();
            $table->longText('internal_notes')->nullable();
            $table->foreignId('batch_id')->nullable()->constrained('batches', 'id');
            $table->foreignId('previous')->nullable()->constrained('season_pages', 'id');
            $table->foreignId('original')->nullable()->constrained('season_pages', 'id');
            $table->foreignId('newest')->nullable()->index()->constrained('season_pages', 'id');
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
        Schema::dropIfExists('season_pages');
    }
};
