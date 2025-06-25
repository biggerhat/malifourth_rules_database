<?php

use App\Http\Controllers\Admin\RoleAdminController;
use App\Http\Controllers\Admin\UserAdminController;

Route::prefix('admin')->middleware(['auth', 'verified'])->name('admin.')->group(function () {
    Route::controller(UserAdminController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{user}', 'edit')->name('edit');
        Route::post('/store', 'store')->name('store');
        Route::post('/update/{user}', 'update')->name('update');
        Route::post('/delete/{user}', 'delete')->name('delete');
    });

    Route::controller(RoleAdminController::class)->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{role}', 'edit')->name('edit');
        Route::post('/store', 'store')->name('store');
        Route::post('/update/{role}', 'update')->name('update');
        Route::post('/delete/{role}', 'delete')->name('delete');
    });
});
