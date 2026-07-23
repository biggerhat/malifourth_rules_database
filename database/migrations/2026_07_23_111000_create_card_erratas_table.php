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
        Schema::create('card_erratas', function (Blueprint $table) {
            $table->id();
            $table->string('faction');
            $table->string('card_name');
            $table->string('slug')->index();
            $table->longText('searchable_text')->nullable();
            $table->longText('internal_notes')->nullable();
            $table->foreignId('batch_id')->nullable()->constrained('batches', 'id');
            $table->foreignId('previous')->nullable()->constrained('card_erratas', 'id');
            $table->foreignId('original')->nullable()->constrained('card_erratas', 'id');
            $table->foreignId('newest')->nullable()->constrained('card_erratas', 'id');
            $table->dateTime('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users', 'id');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['published_at', 'newest'], 'card_erratas_published_at_newest_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_erratas');
    }
};
