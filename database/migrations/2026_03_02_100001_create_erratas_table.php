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
        Schema::create('erratas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->index();
            $table->longText('content')->nullable();
            $table->longText('searchable_text')->nullable();
            $table->longText('internal_notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('batch_id')->nullable()->constrained('batches', 'id');
            $table->foreignId('previous')->nullable()->constrained('erratas', 'id');
            $table->foreignId('original')->nullable()->constrained('erratas', 'id');
            $table->foreignId('newest')->nullable()->index()->constrained('erratas', 'id');
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
        Schema::dropIfExists('erratas');
    }
};
