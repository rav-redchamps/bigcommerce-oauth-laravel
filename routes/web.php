<?php

use Illuminate\Support\Facades\Route;
use MadBoy\BigCommerceAuth\Http\Controllers\BigInstallController;
use MadBoy\BigCommerceAuth\Http\Controllers\BigLoadController;

Route::group([
    'prefix' => 'auth'
], function () {

    Route::get('install', [BigInstallController::class, 'install']);

    Route::get('load', [BigLoadController::class, 'load']);

});
