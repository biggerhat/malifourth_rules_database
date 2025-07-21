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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug')->index();
            $table->longText('left_column')->nullable();
            $table->longText('right_column')->nullable();
            $table->longText('searchable_text')->nullable();
            $table->longText('internal_notes')->nullable();
            $table->integer('page_number');
            $table->string('book_page_numbers')->nullable();
            $table->foreignId('batch_id')->nullable()->constrained('batches', 'id');
            $table->foreignId('previous')->nullable()->constrained('pages', 'id');
            $table->foreignId('original')->nullable()->constrained('pages', 'id');
            $table->foreignId('newest')->index()->nullable()->constrained('pages', 'id');
            $table->dateTime('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users', 'id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pageables', function (Blueprint $table) {
            $table->id();
            $table->morphs('pageable');
            $table->foreignId('page_id')->constrained('pages', 'id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pageables');
        Schema::dropIfExists('pages');
    }
};
