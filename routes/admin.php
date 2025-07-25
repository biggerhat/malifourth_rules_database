<?php

use App\Http\Controllers\Admin\ApprovalAdminController;
use App\Http\Controllers\Admin\BatchAdminController;
use App\Http\Controllers\Admin\IndexAdminController;
use App\Http\Controllers\Admin\PageAdminController;
use App\Http\Controllers\Admin\PageOrderController;
use App\Http\Controllers\Admin\RoleAdminController;
use App\Http\Controllers\Admin\SectionAdminController;
use App\Http\Controllers\Admin\UserAdminController;

Route::prefix('admin')->middleware(['auth', 'verified'])->name('admin.')->group(function () {
    Route::controller(UserAdminController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware(['permission:view_user']);
        Route::get('/create', 'create')->name('create')->middleware(['permission:add_user']);
        Route::get('/edit/{user}', 'edit')->name('edit')->middleware(['permission:edit_user']);
        Route::post('/store', 'store')->name('store')->middleware(['permission:add_user']);
        Route::post('/update/{user}', 'update')->name('update')->middleware(['permission:edit_user']);
        Route::post('/delete/{user}', 'delete')->name('delete')->middleware(['permission:delete_user']);
    });

    Route::controller(RoleAdminController::class)->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware(['permission:view_role']);
        Route::get('/create', 'create')->name('create')->middleware(['permission:add_role']);
        Route::get('/edit/{role}', 'edit')->name('edit')->middleware(['permission:edit_role']);
        Route::post('/store', 'store')->name('store')->middleware(['permission:add_role']);
        Route::post('/update/{role}', 'update')->name('update')->middleware(['permission:edit_role']);
        Route::post('/delete/{role}', 'delete')->name('delete')->middleware(['permission:delete_role']);
    });

    Route::controller(BatchAdminController::class)->prefix('batches')->name('batches.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware(['permission:view_batch']);
        Route::get('/create', 'create')->name('create')->middleware(['permission:add_batch']);
        Route::get('/edit/{batch}', 'edit')->name('edit')->middleware(['permission:edit_batch']);
        Route::post('/store', 'store')->name('store')->middleware(['permission:add_batch']);
        Route::post('/update/{batch}', 'update')->name('update')->middleware(['permission:edit_batch']);
        Route::post('/delete/{batch}', 'delete')->name('delete')->middleware(['permission:delete_batch']);
        Route::post('/publish/{batch}', 'publish')->name('publish')->middleware(['permission:publish_batch']);
    });

    Route::controller(IndexAdminController::class)->prefix('indices')->name('indices.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware(['permission:view_index']);
        Route::get('/view/{index}', 'view')->name('view')->middleware(['permission:view_index']);
        Route::get('/create', 'create')->name('create')->middleware(['permission:add_index']);
        Route::get('/list', 'list')->name('list');
        Route::get('/edit/{index}', 'edit')->name('edit')->middleware(['permission:edit_index']);
        Route::post('/store', 'store')->name('store')->middleware(['permission:add_index']);
        Route::post('/update/{index}', 'update')->name('update')->middleware(['permission:edit_index']);
        Route::post('/delete/{index}', 'delete')->name('delete')->middleware(['permission:delete_index']);
        Route::post('/publish/{index}', 'publish')->name('publish')->middleware(['permission:publish_index']);
    });

    Route::controller(SectionAdminController::class)->prefix('sections')->name('sections.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware(['permission:view_section']);
        Route::get('/view/{section}', 'view')->name('view')->middleware(['permission:view_section']);
        Route::post('/preview', 'preview')->name('preview')->middleware(['permission:view_section']);
        Route::get('/list', 'list')->name('list');
        Route::get('/create', 'create')->name('create')->middleware(['permission:add_section']);
        Route::get('/edit/{section}', 'edit')->name('edit')->middleware(['permission:edit_section']);
        Route::post('/store', 'store')->name('store')->middleware(['permission:add_section']);
        Route::post('/update/{section}', 'update')->name('update')->middleware(['permission:edit_section']);
        Route::post('/delete/{section}', 'delete')->name('delete')->middleware(['permission:delete_section']);
        Route::post('/publish/{section}', 'publish')->name('publish')->middleware(['permission:publish_section']);
    });

    Route::controller(PageAdminController::class)->prefix('pages')->name('pages.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware(['permission:view_page']);
        Route::get('/view/{page}', 'view')->name('view')->middleware(['permission:view_page']);
        Route::get('/list', 'list')->name('list');
        Route::get('/create', 'create')->name('create')->middleware(['permission:add_page']);
        Route::get('/edit/{page}', 'edit')->name('edit')->middleware(['permission:edit_page']);
        Route::post('/store', 'store')->name('store')->middleware(['permission:add_page']);
        Route::post('/update/{page}', 'update')->name('update')->middleware(['permission:edit_page']);
        Route::post('/delete/{page}', 'delete')->name('delete')->middleware(['permission:delete_page']);
        Route::post('/publish/{page}', 'publish')->name('publish')->middleware(['permission:publish_page']);
        Route::prefix('order')->name('order.')->group(function () {
            Route::get('/', [PageOrderController::class, 'index'])->name('index');
            Route::post('/update', [PageOrderController::class, 'update'])->name('update');
        });
    });

    Route::controller(ApprovalAdminController::class)->prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/edit/{approval}', 'edit')->name('edit');
        Route::post('/update/{approval}', 'update')->name('update');
    });

});
