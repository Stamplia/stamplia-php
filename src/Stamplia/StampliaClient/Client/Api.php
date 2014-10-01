<?php
/**
 * Api.php
 *
 * Created By: jonathan
 * Date: 20/01/14
 * Time: 13:57
 */

namespace Stamplia\StampliaClient\Client;

use Stamplia\StampliaClient\Exception\StampliaApiException;
use Stamplia\StampliaClient\Provider\Stamplia;
use Guzzle\Http\StaticClient as GuzzleClient;
use Guzzle\Common\Collection;
use Guzzle\Service\Builder\ServiceBuilder;
use League\OAuth2\Client\Provider\IdentityProvider;

use Guzzle\Http\Client;


class Api {
    protected $provider;
    protected $accessToken;
    protected $refreshToken;
    protected $accessTokenExpires;

    protected $allowedMethods;
    protected $domain;
    protected $protocol = 'https';
    protected $apiUrl = '/api';

    protected $baseUrl;

    public function __construct(IdentityProvider $provider, $accessToken = null, $domain = 'stamplia.com')
    {
        $this->provider = $provider;
        $this->domain = $domain;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function setRefreshToken($token)
    {
        $this->refreshToken = $token;
    }

    public function setAccessTokenExpires($accessTokenExpires)
    {
        $this->accessTokenExpires = $accessTokenExpires;
    }

    public function getAccessTokenExpires()
    {
        return $this->accessTokenExpires;
    }

    public function setAccessToken($accessToken = null, $expires = null, $refreshToken = null)
    {
        $this->accessToken = $accessToken;
        $this->accessTokenExpires = $expires;
        $this->refreshToken = $refreshToken;

        if(!$this->accessToken) {
            if ( ! isset($_GET['code'])) {
                // If we don't have an authorization code then get one
                $this->provider->authorize();
            } else {
                try {
                    // Try to get an access token (using the authorization code grant)

                    $tokens = $this->provider->getAccessToken('authorization_code', array('code' => $_GET['code']));

                    $this->accessToken = $tokens->accessToken;
                    $this->accessTokenExpires = $tokens->expires;
                    $this->refreshToken = $tokens->refreshToken;
                    //TODO save the access token in your database

                } catch (\Exception $e) {
                    echo 'Failed to get access token '.$e->getMessage();
                }
            }
        }elseif($this->accessTokenExpires <= time()) { //token is expired, get a new one from refresh token
            try {
                // Try to get an access token (using the refresh token grant)

                $tokens = $this->provider->refreshAccessToken('refresh_token', array('refresh_token' => $this->refreshToken));

                $this->accessToken = $tokens->accessToken;
                $this->accessTokenExpires = $tokens->expires;
                $this->refreshToken = $tokens->refreshToken;
                //TODO save the access tokens in your database

            } catch (\Exception $e) {
                echo 'Failed to get access token '.$e->getMessage();
            }
        }
    }

    public function getAllowedMethods(){
        return array(
            'createUser' => array(
                'method' => 'post',
                'url' => '/users',
                'parameters' => array('email', 'name', 'language_code', 'type', 'password', 'paypal_email', 'company','address', 'zip', 'country', 'avatar', 'vat', 'client_id'),
                'namespace' => 'user',
            ),
            'getUser' => array(
                'method' => 'get',
                'url' => '/users/{id}',
                'parameters' => array('id'),
                'namespace' => 'user',
            ),
            'getUserMe' => array(
                'method' => 'get',
                'url' => '/users/me',
            ),
            'putUser' => array(
                'method' => 'put',
                'url' => '/users/{id}',
                'parameters' => array('id', 'email', 'name', 'language_code', 'type', 'password', 'paypal_email', 'company','address', 'zip', 'country', 'avatar', 'vat'),
                'namespace' => 'user',
            ),
            'getCategories' => array(
                'method' => 'get',
                'url' => '/categories',
                'parameters' => array('top_level'),
                'namespace' => 'categories',
            ),
            'getCategory' => array(
                'method' => 'get',
                'url' => '/categories/{name}',
                'parameters' => array('name'),
                'namespace' => 'category',
            ),
            'getCategoryTemplates' => array(
                'method' => 'get',
                'url' => '/categories/{name}/templates',
                'parameters' => array('name', 'page', 'per_page', 'order', 'dir'),
                'namespace' => 'templates',
            ),
            'getTemplates' => array(
                'method' => 'get',
                'url' => '/templates',
                'parameters' => array('page', 'per_page', 'order', 'dir', 'category'),
                'namespace' => 'templates',
            ),
            'getTemplate' => array(
                'method' => 'get',
                'url' => '/templates/{slug}',
                'parameters' => array('slug'),
                'namespace' => 'template',
            ),
            'getTemplateLitmustests' => array(
                'method' => 'get',
                'url' => '/templates/{id}/litmustests',
                'parameters' => array('id'),
                'namespace' => 'litmustests',
            ),
            'postZip' => array(
                'method' => 'post',
                'url' => '/users/{userId}/zip',
                'parameters' => array('userId', 'file'),
            ),
            'getUserTemplates' => array(
                'method' => 'get',
                'url' => '/users/{userId}/templates',
                'parameters' => array('userId'),
                'namespace' => 'templates',
            ),
            'getUserTemplate' => array(
                'method' => 'get',
                'url' => '/users/{userId}/templates/{templateId}',
                'parameters' => array('userId', 'templateId'),
                'namespace' => 'template',
            ),
            'getUserPurchases' => array(
                'method' => 'get',
                'url' => '/users/{userId}/purchases',
                'parameters' => array('userId'),
                'namespace' => 'purchases',
            ),
            'postUserPurchases' => array(
                'method' => 'post',
                'url' => '/users/{userId}/purchases',
                'parameters' => array('userId', 'coupon'),
                'namespace' => 'purchase',
            ),
            'makePayment' => array(
                'method' => 'post',
                'url' => '/users/{userId}/invoices/{invoiceId}/payments',
                'parameters' => array('userId', 'invoiceId', 'method', 'redirect_uri'),
            ),
            'getPayment' => array(
                'method' => 'get',
                'url' => '/users/{userId}/invoices/{invoiceId}/payments',
                'parameters' => array('userId', 'invoiceId'),
            ),
            'getUserPurchase' => array(
                'method' => 'get',
                'url' => '/users/{userId}/purchases/{purchaseId}',
                'parameters' => array('userId', 'purchaseId'),
                'namespace' => 'purchases',
            ),
            'postUserTemplate' => array(
                'method' => 'post',
                'url' => '/users/{userId}/templates',
                'parameters' => array('userId', 'name', 'preview_url', 'description', 'zip_path', 'currency_code', 'price', 'draft', 'responsive', 'tags', 'color_codes', 'category'),
                'namespace' => 'template',
            ),
            'putUserTemplate' => array(
                'method' => 'post',
                'url' => '/users/{userId}/templates/{templateId}',
                'parameters' => array('userId','templateId', 'name', 'preview_url', 'description', 'zip_path', 'currency_code', 'price', 'draft', 'responsive', 'tags', 'color_codes', 'category'),
                'namespace' => 'template',
            ),
            'postCart' => array(
                'method' => 'post',
                'url' => '/carts',
                'parameters' => array('user', 'coupon', 'templates'),
                'namespace' => 'cart',
            ),
            'putCart' => array(
                'method' => 'put',
                'url' => '/carts/{id}',
                'parameters' => array('id', 'coupon', 'templates'),
                'namespace' => 'cart',
            ),
            'deleteCart' => array(
                'method' => 'delete',
                'url' => '/carts/{id}',
                'parameters' => array('id'),
                'namespace' => 'cart',
            ),
            'getCart' => array(
                'method' => 'get',
                'url' => '/carts/{id}',
                'parameters' => array('id'),
                'namespace' => 'cart',
            ),
        );
    }

    public function __call($name, $arguments) {
        $methods = $this->getAllowedMethods();
        if(!isset($methods[$name])) {
            throw new StampliaApiException('Method '.$name.' is not supported');
        }

        $anonymousActions = array(
            'createUser',
            'getTemplates',
            'getTemplateLitmustests',
            'getTemplate',
            'getCategories',
            'getCategory',

            'getCategoryTemplates',
        );

        $action = $methods[$name];

        if(!$this->accessToken && !in_array($name, $anonymousActions)) {
            $this->setAccessToken();
        }

        $data = array();
        $namespace = null;
        if(isset($action['namespace'])) {
            $namespace = $action['namespace'];
        }

        if(isset($arguments[0]) && is_array($arguments[0])) {
            $parameters = $arguments[0];

            foreach($parameters as $key => $val) {
                if(in_array($key, $action['parameters'])) {

                    if(strpos($action['url'], '{'.$key.'}') !== false) {
                        $action['url'] = str_replace('{'.$key.'}', $val, $action['url']);
                    } else {
                        $data[$key] = $val;
                    }
                }
            }
        }

        if($name == 'createUser') {
            $data['client_id'] = $this->provider->clientId;
        }

        //TODO replace parameters in URL

        $url = $action['url'];

        return $this->request($action['method'], $url, $data, $namespace);
    }

    public function request($method, $url, $data = null, $namespace = null) {
        try {

            $client = new Client($this->getBaseUrl());

            switch (strtolower($method)) {
                case 'get':
                    $query = array_merge($data, array('access_token' => $this->accessToken));
                    $request = $client->get(
                        $this->apiUrl.$url,
                        array(
                            'Authorization' => 'bearer '.$this->accessToken,
                            'Accept' => 'application/json',
                        ),
                        array(

                            'query' => $query,
                            'debug' => false,
                        )
                    );
                    $response = $request->send();
                    break;
                case 'post':
                    $request = $client->post(
                        $this->apiUrl.$url,
                        array(
                            'Authorization' => 'bearer '.$this->accessToken,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ),
                        json_encode($data),
                        array(
                            'query' => array('access_token' => $this->accessToken),
                            'debug' => false,
                        )
                    );
                    $response = $request->send();
                    break;
                case 'put':
                    $request = $client->put(
                        $this->apiUrl.$url,
                        array(
                            'Authorization' => 'bearer '.$this->accessToken,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ),
                        json_encode($data),
                        array(
                            'query' => array('access_token' => $this->accessToken),
                            'debug' => false,
                        )
                    );
                    $response = $request->send();
                    break;
                case 'delete':
                    $request = $client->delete(
                        $this->apiUrl.$url,
                        array(
                            'Authorization' => 'bearer '.$this->accessToken,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ),
                        null,
                        array(
                            'query' => array('access_token' => $this->accessToken),
                            'debug' => false,
                        )
                    );
                    $response = $request->send();
                    break;
            }


            $body = $response->getBody();
            $a = $body->__toString();
            $r = json_decode($a);

            if($namespace && !isset($r->count)) {

                return $r->{$namespace};
            }
            return $r;

        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $raw_response = explode("\n", $e->getResponse());
            //var_dump($e);
            throw new StampliaApiException(end($raw_response));
        }
    }


    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }
    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->protocol.'://'.$this->domain.$this->apiUrl;
    }


}