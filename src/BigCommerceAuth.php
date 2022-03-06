<?php

namespace MadBoy\BigCommerceAuth;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class BigCommerceAuth
{
    /**
     * BigCommerce App client_id
     * @var string
     */
    protected string $client_id;

    /**
     * BigCommerce App secret
     * @var string
     */
    protected string $secret;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (Config::get('app.env') === 'local') {
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
    public function getClientId(): string
    {
        return $this->client_id;
    }

    /**
     * @param string $client_id
     */
    public function setClientId(mixed $client_id): void
    {
        $this->client_id = $client_id;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret(mixed $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @throws Exception
     */
    public function getRedirectURL(): string
    {
        $redirect_url = Config::get('bigcommerce-auth.redirect_url');
        if (!$redirect_url) {
            throw new Exception('BC_REDIRECT_URL is not set. Please set redirect url.');
        }
        return $redirect_url;
    }

    /**
     * @throws Exception
     */
    public function install(string $code, string $scope, string $context): array|false
    {
        $response = Http::post('https://login.bigcommerce.com/oauth2/token', [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getSecret(),
            'context' => $context,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectURL(),
            'scope' => $scope,
        ]);

        if (!$response->ok() && !$response->successful()) return false;

        return json_decode($response->body(), true);
    }

    /**
     * @param $signedRequest
     * @return array|false
     */
    private function verifySignedPayload($signedRequest): array|false
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
}