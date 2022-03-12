<?php

namespace MadBoy\BigCommerceAuth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|false install(string $code, string $scope, string $context)
 * @method static array|false verifySignedPayload(string $signedRequest)
 * @method static string|false getStoreHash()
 * @method static void setStoreHash(string $store_hash)
 */
class BigCommerceAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bigcommerce-auth';
    }
}