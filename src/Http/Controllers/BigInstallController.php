<?php

namespace MadBoy\BigCommerceAuth\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use MadBoy\BigCommerceAuth\Facades\BigCommerceAuth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BigInstallController extends Controller
{
    public function install(Request $request)
    {
        $this->validatePerms($request);

        $this->saveInformation($request);
    }

    /**
     * Validate Parameters
     * @param Request $request
     * @return void
     */
    protected function validatePerms(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'scope' => 'required|string',
            'context' => 'required|string',
        ]);
    }

    /**
     * Fetch and Save User and Store Information in database
     * @param Request $request
     * @return void
     */
    protected function saveInformation(Request $request)
    {
        $response = BigCommerceAuth::install(
            $request->get('code'),
            $request->get('scope'),
            $request->get('context')
        );

        if ($response) {
            $user = $this->saveUserIfNotExist($response['user']['email']);
            $store = $this->saveStoreIfNotExist($response['context'], $response['access_toke']);
            if (isset($user->id) && isset($store->id)) {
                $this->assignUserToStore($user->id, $store->id);
            }
        }
    }

    private function assignUserToStore($user_id, $store_id)
    {
        $store_has_users = Config::get('bigcommerce-auth.tables.store_has_users');
        DB::table($store_has_users)->updateOrInsert([
            'store_id' => $store_id,
            'user_id' => $user_id,
        ], [
            'store_id' => $store_id,
            'user_id' => $user_id,
        ]);
    }

    private function saveStoreIfNotExist(string $context, string $access_token): Model|Builder
    {
        $hash = explode('/', $context);
        $hash = $hash[1] ?? false;
        if (!$hash) {
            throw new HttpException(500, 'Store hash does not found in context!');
        }
        return $this->getStoreModelClass()::query()->firstOrCreate([
            'hash' => $hash,
            'access_token' => $access_token,
        ], [
            'hash' => $hash,
            'access_token' => $access_token,
        ]);
    }

    private function saveUserIfNotExist($email): Model|Builder
    {
        return $this->getUserModelClass()::query()->firstOrCreate([
            'email' => $email
        ]);
    }

    private function getUserModelClass(): Model
    {
        return Config::get('auth.providers.user.model');
    }

    private function getStoreModelClass(): Model
    {
        return Config::get('bigcommerce-auth.models.store_model');
    }
}