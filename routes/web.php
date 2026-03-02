<?php

use App\Http\Controllers\CommandController;
use App\Http\Controllers\IndexController as IndexPageController;
use App\Http\Controllers\Rules\ErrataController;
use App\Http\Controllers\Rules\FaqController;
use App\Http\Controllers\Rules\GainingGroundsController;
use App\Http\Controllers\Rules\IndexController;
use App\Http\Controllers\Rules\PageController;
use App\Http\Controllers\Rules\SearchController;
use App\Http\Controllers\Rules\SectionController;
use Illuminate\Support\Facades\Route;

Route::get('/command', CommandController::class)->name('command');

Route::get('/', IndexPageController::class)->name('index');

Route::prefix('rules')->name('rules.')->group(function () {
    Route::get('/', [PageController::class, 'index'])->name('index');
    Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
    Route::get('/faq/{faq}', [FaqController::class, 'view'])->name('faq.view')->withTrashed();
    Route::get('/faq/{faq}/history', [FaqController::class, 'viewHistory'])->name('faq.history')->withTrashed();
    Route::get('/indices/{index}', [IndexController::class, 'view'])->name('index.view')->withTrashed();
    Route::get('/sections/{section}', [SectionController::class, 'view'])->name('section.view')->withTrashed();
    Route::get('/pages/{page}/history', [PageController::class, 'viewHistory'])->name('page.history')->withTrashed();
    Route::get('/sections/{section}/history', [SectionController::class, 'viewHistory'])->name('section.history')->withTrashed();
    Route::get('/indices/{index}/history', [IndexController::class, 'viewHistory'])->name('index.history')->withTrashed();

    Route::prefix('gaining-grounds')->name('gaining-grounds.')->group(function () {
        Route::get('/', [GainingGroundsController::class, 'index'])->name('index');
        Route::get('/strategies/{strategy}', [GainingGroundsController::class, 'viewStrategy'])->name('strategy')->withTrashed();
        Route::get('/strategies/{strategy}/history', [GainingGroundsController::class, 'viewStrategyHistory'])->name('strategy.history')->withTrashed();
        Route::get('/schemes/{scheme}', [GainingGroundsController::class, 'viewScheme'])->name('scheme')->withTrashed();
        Route::get('/schemes/{scheme}/history', [GainingGroundsController::class, 'viewSchemeHistory'])->name('scheme.history')->withTrashed();
        Route::get('/{season}/overview', [GainingGroundsController::class, 'overview'])->name('season.overview')->withTrashed();
        Route::get('/{season}/history', [GainingGroundsController::class, 'viewSeasonHistory'])->name('season.history')->withTrashed();
        Route::get('/{season}/{seasonPage}', [GainingGroundsController::class, 'viewSeasonPage'])->name('season-page')->withTrashed();
        Route::get('/{season}/{seasonPage}/history', [GainingGroundsController::class, 'viewSeasonPageHistory'])->name('season-page.history')->withTrashed();
        Route::get('/{season}', [GainingGroundsController::class, 'viewSeason'])->name('season')->withTrashed();
    });

    Route::get('/{page}', [PageController::class, 'view'])->name('page.view')->withTrashed();
});

Route::get('/faq', function () {
    return redirect()->route('rules.faq.index');
})->name('faq.index');

Route::get('/search', [SearchController::class, 'view'])->name('search');

Route::get('/errata', [ErrataController::class, 'index'])->name('errata.index');
Route::get('/errata/batch/{batch}', [ErrataController::class, 'viewBatch'])->name('errata.batch');
Route::get('/errata/{errata}', [ErrataController::class, 'view'])->name('errata.view')->withTrashed();
Route::get('/errata/{errata}/history', [ErrataController::class, 'viewHistory'])->name('errata.history')->withTrashed();

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
