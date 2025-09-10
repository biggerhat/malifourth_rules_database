<?php

use App\Http\Controllers\CommandController;
use App\Http\Controllers\IndexController as IndexPageController;
use App\Http\Controllers\Rules\IndexController;
use App\Http\Controllers\Rules\PageController;
use App\Http\Controllers\Rules\SearchController;
use App\Http\Controllers\Rules\SectionController;
use Illuminate\Support\Facades\Route;

Route::get('/command', CommandController::class)->name('command');

Route::get('/', IndexPageController::class)->name('index');

Route::prefix('rules')->name('rules.')->group(function () {
    Route::get('/', [PageController::class, 'index'])->name('index');
    Route::get('/{page}', [PageController::class, 'view'])->name('page.view')->withTrashed();
    Route::get('/indices/{index}', [IndexController::class, 'view'])->name('index.view')->withTrashed();
    Route::get('/sections/{section}', [SectionController::class, 'view'])->name('section.view')->withTrashed();
});

Route::get('/search', [SearchController::class, 'view'])->name('search');

Route::get('/gaining_grounds', function () {
    return inertia('ComingSoon');
})->name('gaining-grounds.index');

Route::get('/faq', function () {
    return inertia('ComingSoon');
})->name('faq.index');

Route::get('/errata', function () {
    return inertia('ComingSoon');
})->name('errata.index');

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
