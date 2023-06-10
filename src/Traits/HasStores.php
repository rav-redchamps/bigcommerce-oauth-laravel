<?php

namespace CronixWeb\BigCommerceAuth\Traits;

use Illuminate\Support\Facades\Config;
use CronixWeb\BigCommerceAuth\Models\Store;

trait HasStores
{
    public function stores()
    {
        return $this->belongsToMany(
            Store::class,
            Config::get('bigcommerce-auth.tables.store_has_users', 'store_has_users')
        );
    }
}