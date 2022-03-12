<?php

namespace MadBoy\BigCommerceAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use MadBoy\BigCommerceAuth\Facades\BigCommerceAuth;

class BigLoadController extends Controller
{
    public function load(Request $request)
    {
        $this->validatePerms($request);

        $redirect_path = Config::get('bigcommerce-auth.redirect_path', '/');

        if ($this->verifyAndLoginUserIfNot($request))
            return Response::redirectTo($redirect_path);

        App::abort(403);
    }

    /**
     * Validate Parameters
     * @param Request $request
     * @return void
     */
    protected function validatePerms(Request $request)
    {
        $request->validate([
            'signed_payload' => 'required|string'
        ]);
    }

    protected function verifyAndLoginUserIfNot(Request $request)
    {
        $signed_payload = BigCommerceAuth::verifySignedPayload($request->get('signed_payload'));
        if ($signed_payload) {
            $user = $this->getUserModelClass()::query()
                ->where('email', $signed_payload['user']['email'])
                ->first();
            if ($user) {
                Auth::login($user);
                BigCommerceAuth::setStoreHash($signed_payload['store_hash']);
                return true;
            }
        }
        return false;
    }

    private function getUserModelClass(): string
    {
        return Config::get('auth.providers.users.model');
    }
}