<?php

namespace CronixWeb\BigCommerceAuth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Store extends Model
{
    protected $fillable = [
        'hash',
        'access_token',
        'domain',
        'status',
        'admin_email',
        'order_email',
        'timezone',
        'language',
        'currency',
        'plan_name',
        'plan_is_trial',
        'industry',
        'default_channel_id',
        'stencil_enabled',
        'multi_storefront_enabled',
        'storefronts_active',
        'raw_information',
        'app_plan',
        'app_plan_status'
    ];

    public function getTable()
    {
        return Config::get('bigcommerce-auth.tables.stores', parent::getTable());
    }

    public function user()
    {
        return $this->belongsToMany(User::class, Config::get('bigcommerce-auth.tables.store_has_users', 'store_has_users'));
    }
}
