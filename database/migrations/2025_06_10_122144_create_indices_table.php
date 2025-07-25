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
        Schema::create('indices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->index();
            $table->string('type');
            $table->string('image')->nullable();
            $table->longText('content')->nullable();
            $table->longText('searchable_text')->nullable();
            $table->longText('internal_notes')->nullable();
            $table->foreignId('batch_id')->nullable()->constrained('batches', 'id');
            $table->foreignId('previous')->nullable()->constrained('indices', 'id');
            $table->foreignId('original')->nullable()->constrained('indices', 'id');
            $table->foreignId('newest')->index()->nullable()->constrained('indices', 'id');
            $table->dateTime('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users', 'id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('indexables', function (Blueprint $table) {
            $table->id();
            $table->morphs('indexable');
            $table->foreignId('index_id')->constrained('indices', 'id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indexables');
        Schema::dropIfExists('indices');
    }
};
