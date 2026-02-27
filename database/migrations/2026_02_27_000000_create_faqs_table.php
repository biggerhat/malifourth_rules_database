<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('slug')->index();
            $table->string('category');
            $table->longText('answer')->nullable();
            $table->longText('searchable_text')->nullable();
            $table->longText('internal_notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('batch_id')->nullable()->constrained('batches', 'id');
            $table->foreignId('previous')->nullable()->constrained('faqs', 'id');
            $table->foreignId('original')->nullable()->constrained('faqs', 'id');
            $table->foreignId('newest')->index()->nullable()->constrained('faqs', 'id');
            $table->dateTime('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users', 'id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('faqables', function (Blueprint $table) {
            $table->id();
            $table->morphs('faqable');
            $table->foreignId('faq_id')->constrained('faqs', 'id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqables');
        Schema::dropIfExists('faqs');
    }
};
