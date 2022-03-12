<?php

namespace MadBoy\BigCommerceAuth\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array|false install(string $code, string $scope, string $context)
 * @method static array|false verifySignedPayload(string $signedRequest)
 * @method static string|false getStoreHash()
 * @method static void setStoreHash(string $store_hash)
 * @method static string|false getStoreAccessToken()
 * @method static void setInstallCallback(Closure $installCallback)
 * @method static void setLoadCallback(Closure $loadCallback)
 * @method static void callInstallCallback($user, $store)
 * @method static void callLoadCallback($user, $store)
 *
 * @see \MadBoy\BigCommerceAuth\BigCommerceAuth
 */
class BigCommerceAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bigcommerce-auth';
    }
}