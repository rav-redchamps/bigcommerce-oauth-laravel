<?php

return [

    /*
    |--------------------------------------------------------------------------
    | BigCommerce APP client id
    |--------------------------------------------------------------------------
    |
    | BigCommerce App client id is an id you will get after registering your app
    | at https://devtools.bigcommerce.com/my/apps. Click on "View Client ID"
    | button. You will see App client id there.
    |
     */
    'client_id' => env('BC_CLIENT_ID'),

    /*
    |--------------------------------------------------------------------------
    | BigCommerce APP secret
    |--------------------------------------------------------------------------
    |
    | BigCommerce App secret is an id you will get after registering your app
    | at https://devtools.bigcommerce.com/my/apps. Click on "View Client ID"
    | button. You will see App secret there.
    |
     */
    'secret' => env('BC_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | BigCommerce APP Local client id
    |--------------------------------------------------------------------------
    |
    | BigCommerce App client id is an id you will get after registering your app
    | at https://devtools.bigcommerce.com/my/apps. Click on "View Client ID"
    | button. You will see App client id there.
    |
     */
    'local_client_id' => env('BC_LOCAL_CLIENT_ID'),

    /*
    |--------------------------------------------------------------------------
    | BigCommerce APP Local secret
    |--------------------------------------------------------------------------
    |
    | BigCommerce App secret is an id you will get after registering your app
    | at https://devtools.bigcommerce.com/my/apps. Click on "View Client ID"
    | button. You will see App secret there.
    |
     */
    'local_secret' => env('BC_LOCAL_SECRET'),
];
