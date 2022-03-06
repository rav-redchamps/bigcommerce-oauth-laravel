<?php

use Illuminate\Support\Facades\Route;
use MadBoy\BigCommerceAuth\Http\Controllers\BigInstallController;

Route::group([
    'prefix' => 'auth'
], function () {

    Route::get('install', [BigInstallController::class, 'install']);

});
