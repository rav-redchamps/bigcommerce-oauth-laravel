<?php

namespace MadBoy\BigCommerceAuth\Facades;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use MadBoy\BigCommerceAuth\Models\Store;

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
 * @method static void setGetStoreAccessTokenCallback(Closure $getStoreAccessTokenCallback)
 * @method static bool|Closure getUninstallStoreCallBack()
 * @method static void setUninstallStoreCallBack(Closure $uninstallStoreCallBack)
 * @method static bool|Closure getRemoveStoreUserCallBack()
 * @method static void setRemoveStoreUserCallBack(Closure $removeStoreUserCallBack)
 * @method static void setFindStoreFromSessionCallBack(Closure $findStoreFromSessionCallBack)
 * @method static Model|Builder|Store store()
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