<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'auth'
], function () {

    Route::get('install', [Config::get('bigcommerce-auth.controllers.install'), 'install'])
        ->name('bigcommerce-install');

    Route::get('load', [Config::get('bigcommerce-auth.controllers.load'), 'load'])
        ->name('bigcommerce-load');

});
