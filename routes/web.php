<?php

use Illuminate\Support\Facades\Route;
use FuryBee\LoggerUi\Http\Controllers\HomeController;
use FuryBee\LoggerUi\Http\Controllers\LogController;


Route::prefix('logger-ui')
    ->middleware('logger-ui')
    ->group(function () {
        Route::post('/logs', [LogController::class, 'index'])->name('form.logs::index');

        Route::get('/{view?}', HomeController::class)->where('view', '(.*)')->name('view.home::index');
    });
