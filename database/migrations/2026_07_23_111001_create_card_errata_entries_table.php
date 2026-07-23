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
        Schema::create('card_errata_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_errata_id')->constrained('card_erratas', 'id')->cascadeOnDelete();
            $table->text('what_changed')->nullable();
            $table->text('what_it_was')->nullable();
            $table->text('what_it_is_now')->nullable();
            $table->string('front_image')->nullable();
            $table->string('back_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_errata_entries');
    }
};
