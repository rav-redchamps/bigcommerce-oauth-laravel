<?php

namespace MadBoy\BigCommerceAuth;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BigAuthServiceProvider extends PackageServiceProvider
{
    /**
     * @param Package $package
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('bigcommerce-auth')
            ->hasMigration('update_users_table')
            ->hasMigration('create_stores_table')
            ->hasMigration('create_store_has_users_table')
            ->hasConfigFile();
    }

    public function register()
    {
        $this->app->bind('bigcommerce-auth', function () {
            return new BigCommerceAuth();
        });
    }
}