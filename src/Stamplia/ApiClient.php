<?php
/**
 * ApiClient.php
 *
 * Copyright Kiwup
 */

namespace Stamplia;

use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Client;


class ApiClient {

    const DEFAULT_API_VERSION     = 'v1';
    const DEFAULT_DOMAIN          = 'api.beta.stamplia.com';
    const DEFAULT_PROTOCOL        = 'https';

    const SSL_CERTIFICATE_CHECK   = false;

    protected $accessToken;
    protected $refreshToken;
    protected $accessTokenExpires;

    protected $allowedMethods;
    protected $baseUrl;

    protected $routesPrefix;

    public function __construct($apiVersion = self::DEFAULT_API_VERSION, $domain = self::DEFAULT_DOMAIN, $protocol = self::DEFAULT_PROTOCOL)
    {
        $this->allowedMethods = self::publicMethods();

        $this->baseUrl = $protocol . '://'.$domain;
        $this->routesPrefix = '/'.$apiVersion;
    }

    /**
     * @return array
     */
    static function publicMethods() {
        return array(
            'signup' => [
                'method'        => 'post',
                'url'           => '/signup',
                'parameters'    => ['email', 'name', 'fullname', 'type', 'password', 'paypal_email', 'company','address', 'zip', 'country', 'city', 'avatar', 'vat'],
                'namespace'     => 'user',
                'anonymous'     => true,
                'description'   => 'Creates a new user account'
            ],
            'login' => [
                'method'        => 'post',
                'url'           => '/login',
                'parameters'    => ['email', 'password', 'app_id', 'app_secret'],
                'anonymous'     => true,
                'description'   => 'Logs in a user'
            ],
            'getUser' => [
                'method'        => 'get',
                'url'           => '/user/profile',
                'parameters'    => [],
                'namespace'     => 'profile',
                'anonymous'     => false,
                'description'   => 'Retrieves a user profile'
            ],
            'putUser' => [
                'method'        => 'put',
                'url'           => '/user/profile',
                'parameters'    => ['email', 'name', 'fullname', 'type', 'password', 'old_password','paypal_email', 'company','address', 'zip', 'country', 'city','avatar', 'vat'],
                'namespace'     => 'profile',
                'anonymous'     => false,
                'description'   => 'Replaces a user profile'
            ],
            'patchUser' => [
                'method'        => 'patch',
                'url'           => '/user/profile',
                'parameters'    => ['email', 'name', 'fullname', 'type', 'password', 'old_password','paypal_email', 'company','address', 'zip', 'country', 'city','avatar', 'vat'],
                'namespace'     => 'profile',
                'anonymous'     => false,
                'description'   => 'Modifies a user profile'
            ],
            'requestPasswordReset' => [
                'method'        => 'post',
                'url'           => '/request-password-reset',
                'parameters'    => ['email'],
                'anonymous'     => true,
                'description'   => 'Requests the user account password to be reset'
            ],
            'resetPassword' => [
                'method'        => 'post',
                'url'           => '/reset-password',
                'parameters'    => ['token', 'password'],
                'anonymous'     => true,
                'description'   => 'Reset the user account password'
            ],
            'getCategories' => [
                'method'        => 'get',
                'url'           => '/categories',
                'parameters'    => ['page', 'per_page', 'order', 'dir', 'theme', 'hideEmpty', 'compatible'],
                'namespace'     => 'categories',
                'anonymous'     => true,
                'description'   => 'Lists template categories'
            ],
            'getCategory' => [
                'method'        => 'get',
                'url'           => '/categories/{name}',
                'parameters'    => ['name'],
                'namespace'     => 'category',
                'anonymous'     => true,
                'description'   => 'Retrieves a template category'
            ],
            'getThemes' => [
                'method'        => 'get',
                'url'           => '/themes',
                'parameters'    => ['page', 'per_page', 'order', 'dir', 'category', 'hideEmpty', 'compatible'],
                'namespace'     => 'themes',
                'anonymous'     => true,
                'description'   => 'Lists template themes'
            ],
            'getTheme' => [
                'method'        => 'get',
                'url'           => '/themes/{name}',
                'parameters'    => ['name'],
                'namespace'     => 'theme',
                'anonymous'     => true,
                'description'   => 'Retrieves details about a template theme'
            ],
            'getTemplates' => [
                'method'        => 'get',
                'url'           => '/templates',
                'parameters'    => ['page', 'per_page', 'order', 'dir', 'category', 'theme', 'creator', 'compatible'],
                'namespace'     => 'templates',
                'anonymous'     => true,
                'description'   => 'Lists templates'
            ],
            'getTemplate' => [
                'method'        => 'get',
                'url'           => '/templates/{templateId}',
                'parameters'    => ['templateId'],
                'namespace'     => 'template',
                'anonymous'     => true,
                'description'   => 'Retrieves details about a template'
            ],
            'getUserTemplates' => [
                'method'        => 'get',
                'url'           => '/user/templates',
                'parameters'    => ['approved', 'draft', 'page', 'order', 'per_page', 'dir'],
                'namespace'     => 'templates',
                'anonymous'     => false,
                'description'   => 'Lists templates that belong to the user'
            ],
            'getUserTemplate' => [
                'method'        => 'get',
                'url'           => '/user/templates/{templateId}',
                'parameters'    => ['templateId'],
                'namespace'     => 'template',
                'anonymous'     => false,
                'description'   => 'Retrieves details about a user template'
            ],
            'getUserPurchases' => [
                'method'        => 'get',
                'url'           => '/user/purchases',
                'parameters'    => ['page', 'per_page', 'order', 'dir'],
                'namespace'     => 'purchases',
                'anonymous'     => false,
                'description'   => 'Lists purchases made by the user'
            ],
            'getUserPurchase' => [
                'method'        => 'get',
                'url'           => '/user/purchases/{purchaseId}',
                'parameters'    => ['purchaseId', 'provider'],
                'namespace'     => 'purchase',
                'anonymous'     => false,
                'description'   => 'Retrieves details about a purchase made by the user'
            ],
            'downloadUserPurchase' => [
                'method'        => 'download',
                'url'           => '/user/purchases/{purchaseId}.{format}',
                'parameters'    => ['purchaseId', 'format', 'provider'],
                'anonymous'     => false,
                'description'   => 'Downloads a template purchased by the user (zip/html)'
            ],
            'postUserPaypalOrder' => [
                'method'        => 'post',
                'url'           => '/user/paypal/order',
                'parameters'    => ['templates', 'packs', 'coupon', 'credit', 'url_redirect', 'url_canceled'],
                'anonymous'     => false,
                'description'   => 'Makes a Paypal order'
            ],
            'postUserPaypalPurchase' => [
                'method'        => 'post',
                'url'           => '/user/paypal/purchase',
                'parameters'    => ['token'],
                'namespace'     => 'invoice',
                'anonymous'     => false,
                'description'   => 'Makes a purchase using Paypal'
            ],
            'postUserThirdpartyPurchase' => [
                'method'        => 'post',
                'url'           => '/user/thirdparty/purchase',
                'parameters'    => ['templates', 'packs'],
                'namespace'     => 'invoice',
                'anonymous'     => false,
                'description'   => 'Makes a purchase via our ThirdParty payment process'
            ],
        );
    }

    public function __call($name, $arguments)
    {
        $methods = $this->allowedMethods;

        if (!isset($methods[$name])) {
            throw new \Exception('Method '.$name.' is not supported');
        }
        $action = $methods[$name];

        $url = $this->routesPrefix . $action['url'];

        $data = array();
        if (isset($arguments[0]) && is_array($arguments[0])) {
            $parameters = $arguments[0];

            foreach ($parameters as $key => $val) {
                if (in_array($key, $action['parameters'])) {
                    if (strpos($url, '{'.$key.'}') !== false) {
                        $url = str_replace('{'.$key.'}', $val, $url);
                    } else {
                        $data[$key] = $val;
                    }
                }
            }
        }

        $namespace = isset($action['namespace']) ? $action['namespace'] : null;
        $anonymous = isset($action['anonymous']) ? $action['anonymous'] : false;

        return $this->request($action['method'], $url, $data, $namespace, $anonymous, $name);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array|null $data
     * @param string|null $namespace
     * @param bool $anonymous
     * @param string|null $name
     * @return mixed
     * @throws ApiException
     * @throws \Exception
     */
    protected function request($method, $url, array $data = null, $namespace = null, $anonymous = false, $name = null)
    {
        try {
            $client = new Client($this->baseUrl);

            $client->setSslVerification(self::SSL_CERTIFICATE_CHECK);

            $method = strtolower($method);

            // returns the raw body
            if ($method === 'download') {
                return $this->processDownload($client, $url, $data, $anonymous);
            }

            $parameters = [ 'Accept' => 'application/json' ];
            if (!$anonymous) {
                $parameters['Authorization'] = 'Bearer '.$this->accessToken;
            }
            $options = [ 'debug' => false ];

            $payload = null;
            if ($method === 'get' || $method === 'upload') {
                if ($method === 'get') {
                    $options['query'] = $data;
                } else {
                    if (!isset($data['file'])) {
                        throw new \Exception('This method requires a file parameter');
                    }
                    $payload = [ 'file' => '@' . $data['file'] ];
                }
            } else {
                $parameters['Content-Type'] = 'application/json';
                $payload = json_encode($data);
            }

            $response = $this->executeRequest($client, $method, $url, $parameters, $options, $payload);

            $content = $response->getBody(true);

            if ($response->getStatusCode() < 200 && $response->getStatusCode() >= 300) {
                throw new ApiException('', $response->getStatusCode(), $url, $method, $content);
            }

            $ret = json_decode($content);
            if ($ret === false) {
                throw new ApiException('Response is invalid json', 200, $url, $method, $content);
            }

            // on login, store the access token
            if ($name === 'login') {
                $this->setAccessToken($ret->access_token, $ret->expires_in + time(), $ret->refresh_token);
            }

            if ($namespace && !isset($ret->count)) {
                return $ret->{$namespace};
            }

            return $ret;
        } catch (BadResponseException $e) {
            throw new ApiException($e->getMessage(), $e->getResponse()->getStatusCode(), $url, $method, $e->getResponse()->getBody(true));
        }
    }

    /**
     * @param array $methods
     */
    public function addAllowedMethods(array $methods) {
        $this->allowedMethods = array_merge($this->allowedMethods, $methods);
    }

    /**
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string|null $accessToken
     * @param int|null $expires
     * @param string|null $refreshToken
     */
    public function setAccessToken($accessToken = null, $expires = null, $refreshToken = null)
    {
        $this->accessToken = $accessToken;
        $this->accessTokenExpires = $expires;
        $this->refreshToken = $refreshToken;
    }



    /**
     * @param Client $client
     * @param string $method
     * @param string $url
     * @param array $parameters
     * @param array $options
     * @param string|null $payload
     * @return \Guzzle\Http\Message\Response
     * @throws \Exception
     */
    protected function executeRequest(Client $client, $method, $url, array $parameters, array $options, $payload = null)
    {
        switch ($method) {
            case 'get':
                $request = $client->get($url, $parameters, $options);
                break;
            case 'post':
            case 'upload':
                $request = $client->post($url, $parameters, $payload, $options);
                break;
            case 'put':
                $request = $client->put($url, $parameters, $payload, $options);
                break;
            case 'patch':
                $request = $client->patch($url, $parameters, $payload, $options);
                break;
            case 'delete':
                $request = $client->delete($url, $parameters, $payload, $options);
                break;
            default:
                throw new \Exception('Invalid method');
                break;
        }

        return $request->send();
    }

    /**
     * @param Client $client
     * @param string $url
     * @param array|null $data
     * @param bool|false $anonymous
     * @return \Guzzle\Http\EntityBodyInterface|string
     * @throws ApiException
     */
    protected function processDownload(Client $client, $url, array $data = null, $anonymous = false)
    {
        $parameters = [];
        if (!$anonymous) {
            $parameters['Authorization'] = 'Bearer '.$this->accessToken;
        }
        $options = [
            'debug' => false,
            'query' => $data
        ];

        // returns the raw body
        try {
            $response = $client->get($url, $parameters, $options)->send();
        } catch (BadResponseException $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $url, 'download', $e->getResponse());
        }

        return $response->getBody();
    }

}
