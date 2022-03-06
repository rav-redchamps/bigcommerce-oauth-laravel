<?php

namespace MadBoy\BigCommerceAuth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|false install(string $code, string $scope, string $context)
 */
class BigCommerceAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bigcommerce-auth';
    }
}