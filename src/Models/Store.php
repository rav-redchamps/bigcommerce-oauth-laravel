<?php

namespace MadBoy\BigCommerceAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Store extends Model
{
    public function getTable()
    {
        return Config::get('bigcommerce-auth.tables.stores', parent::getTable());
    }
}