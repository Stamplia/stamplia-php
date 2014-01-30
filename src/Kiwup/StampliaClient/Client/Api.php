<?php
/**
 * Api.php
 *
 * Created By: jonathan
 * Date: 20/01/14
 * Time: 13:57
 */

namespace Kiwup\StampliaClient\Client;

use Kiwup\StampliaClient\Exception\StampliaApiException;
use Kiwup\StampliaClient\Provider\Stamplia;
use Guzzle\Http\StaticClient as GuzzleClient;
use Guzzle\Common\Collection;
use Guzzle\Service\Builder\ServiceBuilder;

class Api{
    protected $provider;
    protected $accessToken;
    protected $allowedMethods;
    protected $baseUrl = 'https://stamplia.com/api';

    public function __construct(Stamplia $provider, $accessToken = null)
    {

        $this->provider = $provider;
        $this->accessToken = $accessToken;

        if($this->accessToken) {
            return;
        }

        if ( ! isset($_GET['code'])) {
            // If we don't have an authorization code then get one
            $this->provider->authorize();
        } else {

            try {
                // Try to get an access token (using the authorization code grant)
                $this->accessToken = $this->provider->getAccessToken('authorization_code', array('code' => $_GET['code']))->accessToken;

                //TODO save the access token in your database

            } catch (\Exception $e) {
                echo 'failed to get access token '.$e->getMessage();
                // Failed to get access token

            }
        }





    }

    public function getAllowedMethods(){
        return array(
            'createUser' => array(
                'method' => 'post',
                'url' => '/users',
                'parameters' => array('email', 'name', 'language_code', 'type', 'password', 'paypal_email', 'company','address', 'zip', 'country', 'avatar', 'vat'),
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
                'parameters' => array('name'),
                'namespace' => 'templates',
            ),
            'getTemplates' => array(
                'method' => 'get',
                'url' => '/templates',
                'parameters' => array('page', 'per_page', 'order', 'dir'),
                'namespace' => 'templates',
            ),
            'getTemplate' => array(
                'method' => 'get',
                'url' => '/templates/{slug}',
                'parameters' => array('slug'),
                'namespace' => 'template',
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




    public function __call($name, $arguments){
        $methods = $this->getAllowedMethods();
        if(!isset($methods[$name])) {
            throw new StampliaApiException('Method '.$name.' is not supported');
        }

        $action = $methods[$name];
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
        //TODO replace parameters in URL

        return $this->request($action['method'], $this->baseUrl.$action['url'], $data, $namespace);
    }

    public function request($method, $url, $data = null, $namespace = null) {
        try {
            switch (strtolower($method)) {
                case 'get':
                    $response = GuzzleClient::get($url, array(
                        'headers' => array(
                            'Authorization' => 'bearer '.$this->accessToken,
                            'Accept' => 'application/json',
                        ),
                        'query' => array('access_token' => $this->accessToken),
                        'debug' => true,
                    ));

                    break;
                case 'post':
                    $response = GuzzleClient::post($url, array(
                        'headers' => array(
                            'Authorization' => 'bearer '.$this->accessToken,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ),
                        'body' => json_encode($data),
                        'query' => array('access_token' => $this->accessToken),
                        'debug' => true,
                    ));
                    break;
                case 'put':
                    $response = GuzzleClient::put($url, array(
                        'headers' => array(
                            'Authorization' => 'bearer '.$this->accessToken,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ),
                        'body' => json_encode($data),
                        'query' => array('access_token' => $this->accessToken),
                        'debug' => true,
                    ));
                    break;
                case 'delete':
                    $response = GuzzleClient::delete($url, array(
                        'headers' => array(
                            'Authorization' => 'bearer '.$this->accessToken,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ),
                        'query' => array('access_token' => $this->accessToken),
                        'debug' => true,
                    ));
                    break;
            }

            $r = json_decode($response->getBody());
            if($namespace) {
                return $r->{$namespace};
            }
            return $r;

        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $raw_response = explode("\n", $e->getResponse());
            throw new StampliaApiException(end($raw_response));

        }
    }

} 