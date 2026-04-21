<?php

use App\Http\Controllers\API\V1\BatchController;
use App\Http\Controllers\API\V1\ErrataController;
use App\Http\Controllers\API\V1\FaqController;
use App\Http\Controllers\API\V1\IndexController;
use App\Http\Controllers\API\V1\PageController;
use App\Http\Controllers\API\V1\SchemeController;
use App\Http\Controllers\API\V1\SearchController;
use App\Http\Controllers\API\V1\SeasonController;
use App\Http\Controllers\API\V1\SeasonPageController;
use App\Http\Controllers\API\V1\SectionController;
use App\Http\Controllers\API\V1\StrategyController;
use Illuminate\Support\Facades\Route;

Route::get('search', SearchController::class)->name('search');

Route::apiResource('batches', BatchController::class)->only(['index', 'show']);
Route::apiResource('errata', ErrataController::class)->only(['index', 'show'])->parameters(['errata' => 'errata']);
Route::apiResource('faqs', FaqController::class)->only(['index', 'show']);
Route::apiResource('indices', IndexController::class)->only(['index', 'show'])->parameters(['indices' => 'index']);
Route::apiResource('pages', PageController::class)->only(['index', 'show']);
Route::apiResource('schemes', SchemeController::class)->only(['index', 'show']);
Route::apiResource('seasons', SeasonController::class)->only(['index', 'show']);
Route::apiResource('season-pages', SeasonPageController::class)->only(['index', 'show'])->parameters(['season-pages' => 'seasonPage']);
Route::apiResource('sections', SectionController::class)->only(['index', 'show']);
Route::apiResource('strategies', StrategyController::class)->only(['index', 'show']);
