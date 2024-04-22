<?php

namespace CronixWeb\BigCommerceAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CronixWeb\BigCommerceAuth\Facades\BigCommerceAuth;

class BigUninstallController extends Controller
{
    public function uninstall(Request $request)
    {
        $this->validatePerms($request);

        $validatedSignedPayload = $this->verifySignedPayload($request);

        $this->removeStoreData($validatedSignedPayload);
    }

    protected function removeStoreData($signedPayload)
    {
        $uninstallCallback = BigCommerceAuth::getUninstallStoreCallBack();

        if ($signedPayload && $uninstallCallback) {
            $uninstallCallback($signedPayload);
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
