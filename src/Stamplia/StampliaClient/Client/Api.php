<?php
/**
 * Api.php
 *
 * Created By: jonathan
 * Date: 20/01/14
 * Time: 13:57
 */

namespace Stamplia\StampliaClient\Client;

use Guzzle\Http\EntityBody;
use Stamplia\StampliaClient\Exception\StampliaApiException;
use Stamplia\StampliaClient\Provider\Stamplia;
use Guzzle\Http\StaticClient as GuzzleClient;
use Guzzle\Common\Collection;
use Guzzle\Service\Builder\ServiceBuilder;
use League\OAuth2\Client\Provider\ProviderInterface;

use Guzzle\Http\Client;


class Api {
    protected $provider;
    protected $accessToken;
    protected $refreshToken;
    protected $accessTokenExpires;

    protected $allowedMethods;
    protected $domain;
    protected $protocol = 'https';
    protected $apiUrl = '/v1';

    protected $baseUrl;

    public function __construct(ProviderInterface $provider, $accessToken = null, $domain = 'stamplia.com')
    {
        $this->provider = $provider;
        $this->domain = $domain;
        $this->allowedMethods = $this->getPublicAllowedMethods();
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

    public function getAllowedMethods() {
        return $this->allowedMethods;
    }
    public function addAllowedMethods(array $methods) {
        $this->allowedMethods = array_merge($this->allowedMethods, $methods);
    }
    protected function getPublicAllowedMethods() {
        return array(
            'signup' => array(
                'method' => 'post',
                'url' => '/signup',
                'parameters' => array('email', 'name', 'fullname', 'language_code', 'type', 'password', 'paypal_email', 'company','address', 'zip', 'country', 'avatar', 'vat'),
                'namespace' => 'user',
            ),
            'login' => array(
                'method' => 'post',
                'url' => '/login',
                'parameters' => array('email', 'password', 'app_id', 'app_secret'),
            ),
            'getUser' => array(
                'method' => 'get',
                'url' => '/user/profile',
                'parameters' => array(),
                'namespace' => 'profile',
            ),
            'putUser' => array(
                'method' => 'put',
                'url' => '/user/profile',
                'parameters' => array('email', 'name', 'fullname', 'language_code', 'type', 'password', 'paypal_email', 'company','address', 'zip', 'country', 'avatar', 'vat'),
                'namespace' => 'profile',
            ),
            'requestPasswordReset'=> array(
                'method' => 'post',
                'url' => '/request-password-reset',
                'parameters' => array('email'),
            ),
            'resetPassword'=> array(
                'method' => 'post',
                'url' => '/reset-password',
                'parameters' => array('token', 'password'),
            ),
            'getCategories' => array(
                'method' => 'get',
                'url' => '/categories',
                'parameters' => array(),
                'namespace' => 'categories',
            ),
            'getCategory' => array(
                'method' => 'get',
                'url' => '/categories/{name}',
                'parameters' => array('name'),
                'namespace' => 'category',
            ),
            'getThemes' => array(
                'method' => 'get',
                'url' => '/themes',
                'parameters' => array(''),
                'namespace' => 'themes',
            ),
            'getTheme' => array(
                'method' => 'get',
                'url' => '/themes/{name}',
                'parameters' => array('name'),
                'namespace' => 'theme',
            ),
            'getCategoryTemplates' => array(
                'method' => 'get',
                'url' => '/categories/{name}/templates',
                'parameters' => array('name', 'page', 'per_page', 'order', 'dir', 'theme'),
                'namespace' => 'templates',
            ),
            'getThemeTemplates' => array(
                'method' => 'get',
                'url' => '/themes/{name}/templates',
                'parameters' => array('name', 'page', 'per_page', 'order', 'dir', 'category'),
                'namespace' => 'templates',
            ),
            'getTemplates' => array(
                'method' => 'get',
                'url' => '/templates',
                'parameters' => array('page', 'per_page', 'order', 'dir', 'category', 'theme', 'creator'),
                'namespace' => 'templates',
            ),
            'getTemplate' => array(
                'method' => 'get',
                'url' => '/templates/{templateId}',
                'parameters' => array('templateId'),
                'namespace' => 'template',
            ),
            'getTemplateLitmustests' => array(
                'method' => 'get',
                'url' => '/litmustests/{templateId}',
                'parameters' => array('templateId'),
                'namespace' => 'litmustests',
            ),
            'getUserTemplateLitmustests' => array(
                'method' => 'get',
                'url' => '/user/litmustests/{templateId}',
                'parameters' => array('templateId'),
                'namespace' => 'litmustests',
            ),
            'getUserTemplates' => array(
                'method' => 'get',
                'url' => '/user/templates',
                'parameters' => array('approved', 'draft', 'page', 'order', 'per_page', 'dir'),
                'namespace' => 'templates',
            ),
            'getUserTemplate' => array(
                'method' => 'get',
                'url' => '/user/templates/{templateId}',
                'parameters' => array('templateId'),
                'namespace' => 'template',
            ),
            'getUserPurchases' => array(
                'method' => 'get',
                'url' => '/user/purchases',
                'parameters' => array(),
                'namespace' => 'purchases',
            ),
            'getUserPurchase' => array(
                'method' => 'get',
                'url' => '/user/purchases/{purchaseId}',
                'parameters' => array('purchaseId', 'provider'),
                'namespace' => 'purchase',
            ),
            'downloadUserPurchase' => array(
                'method' => 'download',
                'url' => '/user/purchases/{purchaseId}.{format}',
                'parameters' => array('purchaseId', 'format', 'provider'),
            ),
            'postUserPaypalOrder'=> array(
                'method' => 'post',
                'url' => '/user/paypal/order',
                'parameters' => array('templates', 'packs', 'coupon', 'credit', 'url_redirect', 'url_canceled'),
            ),
            'postUserPaypalPurchase'=> array(
                'method' => 'post',
                'url' => '/user/paypal/purchase',
                'parameters' => array('token'),
                'namespace' => 'invoice',
            ),
            'postUserThirdpartyPurchase'=> array(
                'method' => 'post',
                'url' => '/user/thirdparty/purchase',
                'parameters' => array('templates', 'packs'),
                'namespace' => 'invoice',
            ),
        );
    }

    public function __call($name, $arguments) {
        $methods = $this->getAllowedMethods();
        if(!isset($methods[$name])) {
            throw new StampliaApiException('Method '.$name.' is not supported');
        }

        $anonymousActions = array(
            'signup',
            'login',
            'requestPasswordReset',
            'resetPassword',
            'getTemplates',
            'getTemplateLitmustests',
            'getTemplate',
            'getCategories',
            'getCategory',
            'getCategoryTemplates',
            'getTheme',
            'getThemes',
            'getThemeTemplates'
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

    public function request($method, $url, $data = null, $namespace = null, $relativeUrl = false) {
        try {

            $client = new Client($this->getBaseUrl());

            $client->setSslVerification(false);
            $url = $relativeUrl ? $url : $this->apiUrl.$url;

            switch (strtolower($method)) {
                case 'get':
//                    $query = array_merge($data, array('access_token' => $this->accessToken));
                    $request = $client->get(
                        $url,
                        array(
                            'Authorization' => 'Bearer '.$this->accessToken,
                            'Accept' => 'application/json',
                        ),
                        array(

                            'query' => $data,
                            'debug' => false,
                        )
                    );
                    $response = $request->send();
                    break;
                case 'post':
                    $request = $client->post(
                        $url,
                        array(
                            'Authorization' => 'Bearer '.$this->accessToken,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ),
                        json_encode($data),
                        array(
//                            'query' => array('access_token' => $this->accessToken),
                            'debug' => false,
                        )
                    );
                    $response = $request->send();
                    break;
                case 'put':
                    $request = $client->put(
                        $url,
                        array(
                            'Authorization' => 'Bearer '.$this->accessToken,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ),
                        json_encode($data),
                        array(
//                            'query' => array('access_token' => $this->accessToken),
                            'debug' => false,
                        )
                    );
                    $response = $request->send();
                    break;
                case 'delete':
                    $request = $client->delete(
                        $url,
                        array(
                            'Authorization' => 'Bearer '.$this->accessToken,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ),
                        null,
                        array(
//                            'query' => array('access_token' => $this->accessToken),
                            'debug' => false,
                        )
                    );
                    $response = $request->send();
                    break;


                case 'download':
                    try {
                        $request = $client->get(
                            $url,
                            array(
                                'Authorization' => 'Bearer '.$this->accessToken,
                            ),
                            array(
                                'query' => $data,
                                'debug' => false,
                            )
                        );
                        $response = $request->send();
                    } catch(\Guzzle\Http\Exception\BadResponseException $e) {
                        $raw_response = explode("\n", $e->getResponse());
                        var_dump($raw_response); die();
                        throw new StampliaApiException(end($raw_response));
                    } catch(\Exception $e) {
                        throw new StampliaApiException($e->getMessage());
                    }

                    return $response->getBody();

                case 'upload':
                    $request = $client->post(
                        $url,
                        array(
                            'Accept' => 'application/json',
                            'Authorization' => 'Bearer '.$this->accessToken,
                        ),
                        array('file'=>'@'.$data['file'])
                    );
                    $response = $request->send();
                    break;

            }

            $body = $response->getBody();
            $a = $body->__toString();
            $r = json_decode($a);

            if($url == $this->apiUrl.'/login') {
                $this->setAccessToken($r->access_token, $r->expires_in + time(), $r->refresh_token);
            }

            if($namespace && !isset($r->count)) {

                return $r->{$namespace};
            }

            return $r;

        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $raw_response = explode("\n", $e->getResponse());
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