<?php

namespace MadBoy\BigCommerceAuth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BigAuthHelper
{
    public static function assignUserToStore($user_id, $store_id): bool
    {
        $store_has_users = Config::get('bigcommerce-auth.tables.store_has_users');
        if (DB::table($store_has_users)
            ->where('store_id', $store_id)
            ->where('user_id', $user_id)
            ->exists())
            return true;

        return DB::table($store_has_users)->insert([
            'store_id' => $store_id,
            'user_id' => $user_id,
        ]);
    }

    /**
     * @return Model
     */
    public static function getUserModelClass(): string
    {
        return Config::get('auth.providers.users.model');
    }

    /**
     * @return Model
     */
    public static function getStoreModelClass(): string
    {
        return Config::get('bigcommerce-auth.models.store_model');
    }
}