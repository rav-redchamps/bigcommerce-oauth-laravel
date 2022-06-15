<?php

namespace MadBoy\BigCommerceAuth\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use MadBoy\BigCommerceAuth\BigAuthHelper;
use MadBoy\BigCommerceAuth\Facades\BigCommerceAuth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BigInstallController extends Controller
{
    public function install(Request $request): \Illuminate\Http\Response|RedirectResponse
    {
        $this->validatePerms($request);

        $redirect_path = Config::get('bigcommerce-auth.redirect_path', '/');

        if ($this->saveInformation($request))
            return Response::redirectTo($redirect_path);

        $error_view = Config::get('bigcommerce-auth.error_view');

        if (!$error_view)
            $error_view = 'bigcommerce-auth::error';

        return Response::view($error_view, status: 500);
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
     * @return bool
     */
    protected function saveInformation(Request $request): bool
    {
        $response = BigCommerceAuth::install(
            $request->get('code'),
            $request->get('scope'),
            $request->get('context')
        );

        if ($response) {
            $user = $this->saveUserIfNotExist($response['user']['email']);
            $store = $this->saveStoreIfNotExist($response['context'], $response['access_token']);
            if (isset($user->id) && isset($store->id)) {
                if (BigAuthHelper::assignUserToStore($user->id, $store->id)) {
                    Auth::login($user);
                    BigCommerceAuth::setStoreHash($store->hash);
                    BigCommerceAuth::callInstallCallback($user, $store);
                    BigCommerceAuth::callLoadCallback($user, $store);
                    return true;
                }
            }
        }

        return false;
    }

    protected function saveStoreIfNotExist(string $context, string $access_token): Model|Builder
    {
        $hash = explode('/', $context);
        $hash = $hash[1] ?? false;
        if (!$hash) {
            throw new HttpException(500, 'Store hash does not found in context!');
        }
        $store = BigAuthHelper::getStoreModelClass()::query()
            ->where('hash', $hash)
            ->first();
        if ($store) {
            $store->access_token = $access_token;
            $store->save();
            return $store;
        }
        return BigAuthHelper::getStoreModelClass()::query()->create([
            'hash' => $hash,
            'access_token' => $access_token,
        ]);
    }

    /**
     * @param $email
     * @return Model|Builder|Authenticatable
     */
    protected function saveUserIfNotExist($email)
    {
        return BigAuthHelper::getUserModelClass()::query()->firstOrCreate([
            'email' => $email
        ]);
    }
}