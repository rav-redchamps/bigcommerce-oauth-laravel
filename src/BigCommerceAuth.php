<?php

namespace MadBoy\BigCommerceAuth;

use Exception;
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

    /**
     * App environment
     * @var string
     */
    private string $environment;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->environment = Config::get('app.env');
        if ($this->environment === 'local') {
            $this->setClientId(Config::get('bigcommerce-auth.local_client_id'));
            $this->setSecret(Config::get('bigcommerce-auth.local_secret'));
        } else {
            $this->setClientId(Config::get('bigcommerce-auth.client_id'));
            $this->setSecret(Config::get('bigcommerce-auth.secret'));
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
        if ($this->environment === 'local') {
            $response = Http::withoutVerifying()->post('https://login.bigcommerce.com/oauth2/token', [
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getSecret(),
                'context' => $context,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->getRedirectURL(),
                'scope' => $scope,
            ]);
        } else {
            $response = Http::post('https://login.bigcommerce.com/oauth2/token', [
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

        $store = Store::query()
            ->select(['id', 'hash'])
            ->where('hash', $store_hash)
            ->first();
        if (!$store)
            return false;

        return (string)$store->hash;
    }
}