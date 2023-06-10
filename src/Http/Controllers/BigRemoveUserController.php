<?php

namespace CronixWeb\BigCommerceAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CronixWeb\BigCommerceAuth\Facades\BigCommerceAuth;

class BigRemoveUserController extends Controller
{
    public function removeUser(Request $request)
    {
        $this->validatePerms($request);

        $validatedSignedPayload = $this->verifySignedPayload($request);

        $this->removeUserData($validatedSignedPayload);
    }

    protected function removeUserData($signedPayload)
    {
        $removeStoreUserCallBack = BigCommerceAuth::getRemoveStoreUserCallBack();

        if ($signedPayload && $removeStoreUserCallBack) {
            $removeStoreUserCallBack($signedPayload);
        }
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

    /**
     * Verify Signed Payload
     * @param Request $request
     * @return bool|array
     */
    protected function verifySignedPayload(Request $request): bool|array
    {
        return BigCommerceAuth::verifySignedPayload($request->get('signed_payload'));
    }
}