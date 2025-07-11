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
        Schema::create('schemes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->index();
            $table->foreignId('season_id')->constrained('seasons', 'id')->cascadeOnDelete();
            $table->longText('prerequisites')->nullable();
            $table->longText('reveal')->nullable();
            $table->longText('scoring')->nullable();
            $table->longText('additional')->nullable();
            $table->longText('searchable_text')->nullable();
            $table->string('front_image');
            $table->string('back_image')->nullable();
            $table->string('combination_image')->nullable();
            $table->foreignId('next_scheme_1')->nullable()->constrained('schemes', 'id');
            $table->foreignId('next_scheme_2')->nullable()->constrained('schemes', 'id');
            $table->foreignId('next_scheme_3')->nullable()->constrained('schemes', 'id');
            $table->longText('internal_notes')->nullable();
            $table->foreignId('batch_id')->nullable()->constrained('batches', 'id');
            $table->foreignId('previous')->nullable()->constrained('schemes', 'id');
            $table->foreignId('original')->nullable()->constrained('schemes', 'id');
            $table->foreignId('newest')->index()->nullable()->constrained('schemes', 'id');
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
        Schema::dropIfExists('schemes');
    }
};
