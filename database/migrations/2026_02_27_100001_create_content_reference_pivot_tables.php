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
        Schema::create('seasonables', function (Blueprint $table) {
            $table->id();
            $table->morphs('seasonable');
            $table->foreignId('season_id')->constrained('seasons', 'id');
        });

        Schema::create('schemeables', function (Blueprint $table) {
            $table->id();
            $table->morphs('schemeable');
            $table->foreignId('scheme_id')->constrained('schemes', 'id');
        });

        Schema::create('strategyables', function (Blueprint $table) {
            $table->id();
            $table->morphs('strategyable');
            $table->foreignId('strategy_id')->constrained('strategies', 'id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategyables');
        Schema::dropIfExists('schemeables');
        Schema::dropIfExists('seasonables');
    }
};
