<?php

namespace MadBoy\BigCommerceAuth;

use Closure;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use MadBoy\BigCommerceAuth\Models\Store;

class BigCommerceAuth
{
    /**
     * BigCommerce App client_id
     * @var string
     */
    private string $client_id;

    /**
     * BigCommerce App secret
     * @var string
     */
    private string $secret;

    private Closure $installCallback;

    private Closure $loadCallback;

    private ?Closure $getStoreAccessTokenCallback;

    private Closure $uninstallStoreCallBack;

    private Closure $removeStoreUserCallBack;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (App::isProduction()) {
            $this->setClientId(Config::get('bigcommerce-auth.client_id'));
            $this->setSecret(Config::get('bigcommerce-auth.secret'));
        } else {
            $this->setClientId(Config::get('bigcommerce-auth.local_client_id'));
            $this->setSecret(Config::get('bigcommerce-auth.local_secret'));
        }
        if ($this->getClientId() == null)
            throw new Exception('BC_CLIENT_ID not set. Please set client id first.');
        if ($this->getSecret() == null)
            throw new Exception('BC_SECRET not set. Please set secret first.');
    }

    /**
     * @return string
     */
    private function getClientId(): string
    {
        return $this->client_id;
    }

    /**
     * @param string $client_id
     */
    private function setClientId(mixed $client_id): void
    {
        $this->client_id = $client_id;
    }

    /**
     * @return string
     */
    private function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    private function setSecret(mixed $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @throws Exception
     */
    private function getRedirectURL(): string
    {
        $redirect_url = Config::get('bigcommerce-auth.redirect_url');
        if (!$redirect_url) {
            throw new Exception('BC_REDIRECT_URL is not set. Please set redirect url.');
        }
        return $redirect_url;
    }

    /**
     * Get session key from config
     * @return string
     */
    private function getSessionKey(): string
    {
        return Config::get('bigcommerce-auth.session_key', 'bigcommerce_auth');
    }

    /**
     * Get store hash session key
     * @return string
     */
    private function getStoreHashSessionKey(): string
    {
        return $this->getSessionKey() . '.store_hash_' . sha1(static::class);
    }

    /**
     * @throws Exception
     */
    public function install(string $code, string $scope, string $context): array|false
    {
        if (App::isProduction()) {
            $response = Http::post('https://login.bigcommerce.com/oauth2/token', [
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getSecret(),
                'context' => $context,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->getRedirectURL(),
                'scope' => $scope,
            ]);
        } else {
            $response = Http::withoutVerifying()->post('https://login.bigcommerce.com/oauth2/token', [
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getSecret(),
                'context' => $context,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->getRedirectURL(),
                'scope' => $scope,
            ]);
        }

        if (!$response->ok() && !$response->successful()) return false;

        return json_decode($response->body(), true);
    }

    /**
     * @param string $signedRequest
     * @return array|false
     */
    public function verifySignedPayload(string $signedRequest): array|false
    {
        list($encodedData, $encodedSignature) = explode('.', $signedRequest, 2);

        // decode the data
        $signature = base64_decode($encodedSignature);
        $jsonStr = base64_decode($encodedData);
        $data = json_decode($jsonStr, true);

        // confirm the signature
        $expectedSignature = hash_hmac('sha256', $jsonStr, $this->getSecret());
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }

        return $data;
    }

    /**
     * Get BigCommerce user requested store hash
     * @return string|false
     */
    public function getStoreHash(): string|false
    {
        return Session::get($this->getStoreHashSessionKey(), false);
    }

    /**
     * Save BigCommerce user requested store hash in session
     * @param string $store_hash
     */
    public function setStoreHash(string $store_hash): void
    {
        Session::put($this->getStoreHashSessionKey(), $store_hash);
    }

    /**
     * Get store access token out of the box
     * Note: you need to set store hash first
     * @return string|bool
     * @throws Exception
     */
    public function getStoreAccessToken(): string|bool
    {
        if (!($store_hash = $this->getStoreHash()))
            throw new Exception('Store hash is not set. Please set store hash using setStoreHash method.');

        if ($this->getGetStoreAccessTokenCallback() != null) {
            return ($this->getGetStoreAccessTokenCallback())($store_hash);
        }

        $store = Store::query()
            ->select(['id', 'access_token'])
            ->where('hash', $store_hash)
            ->first();
        if (!$store)
            return false;

        return (string)$store->access_token;
    }

    /**
     * Set install callback function that will execute after install done
     * @param Closure $installCallback
     */
    public function setInstallCallback(Closure $installCallback): void
    {
        $this->installCallback = $installCallback;
    }

    /**
     * Set callback function that will execute after load done
     * @param Closure $loadCallback
     */
    public function setLoadCallback(Closure $loadCallback): void
    {
        $this->loadCallback = $loadCallback;
    }

    /**
     * Execute install callback
     * @param $user
     * @param $store
     */
    public function callInstallCallback($user, $store)
    {
        if (isset($this->installCallback))
            ($this->installCallback)($user, $store);
    }

    /**
     * @param $user
     * @param $store
     * @return void
     */
    public function callLoadCallback($user, $store)
    {
        if (isset($this->loadCallback))
            ($this->loadCallback)($user, $store);
    }

    /**
     * @return Closure|null
     */
    public function getGetStoreAccessTokenCallback(): ?Closure
    {
        return $this->getStoreAccessTokenCallback;
    }

    /**
     * @param Closure $getStoreAccessTokenCallback
     */
    public function setGetStoreAccessTokenCallback(Closure $getStoreAccessTokenCallback): void
    {
        $this->getStoreAccessTokenCallback = $getStoreAccessTokenCallback;
    }

    /**
     * @return Closure|false
     */
    public function getUninstallStoreCallBack(): bool|Closure
    {
        return $this->uninstallStoreCallBack ?? false;
    }

    /**
     * @param Closure $uninstallStoreCallBack
     */
    public function setUninstallStoreCallBack(Closure $uninstallStoreCallBack): void
    {
        $this->uninstallStoreCallBack = $uninstallStoreCallBack;
    }

    /**
     * @return Closure|false
     */
    public function getRemoveStoreUserCallBack(): bool|Closure
    {
        return $this->removeStoreUserCallBack ?? false;
    }

    /**
     * @param Closure $removeStoreUserCallBack
     */
    public function setRemoveStoreUserCallBack(Closure $removeStoreUserCallBack): void
    {
        $this->removeStoreUserCallBack = $removeStoreUserCallBack;
    }
}