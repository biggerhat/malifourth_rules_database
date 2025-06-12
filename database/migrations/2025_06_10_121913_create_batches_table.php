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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('release_notes')->nullable();
            $table->longText('internal_notes')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->foreignId('published_by')->constrained('users', 'id');
            $table->foreignId('created_by')->constrained('users', 'id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->morphs('approveable');
            $table->json('change_notes')->nullable();
            $table->longText('internal_notes');
            $table->foreignId('initiated_by')->nullable()->constrained('users', 'id');
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users', 'id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approveables');
        Schema::dropIfExists('batches');
    }
};
