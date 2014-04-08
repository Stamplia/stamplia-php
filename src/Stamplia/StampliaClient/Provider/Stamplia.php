<?php
/**
 * Stamplia.php
 *
 * Created By: jonathan
 * Date: 20/01/14
 * Time: 11:38
 */

namespace Stamplia\StampliaClient\Provider;

use League\OAuth2\Client\Provider\IdentityProvider;
use League\OAuth2\Client\Provider\User;
use League\OAuth2\Client\Token\AccessToken;
use Guzzle\Service\Client as GuzzleClient;
use League\OAuth2\Client\Exception\IDPException as IDPException;
use League\OAuth2\Client\Grant\GrantInterface;
use Stamplia\StampliaClient\Grant\Refreshtoken;

class Stamplia extends IdentityProvider{

    protected $domain = 'https://stamplia.com';

    public function urlAuthorize()
    {
        return $this->domain.'/authorize';
    }

    public function urlAccessToken()
    {
        return $this->domain.'/oauth/v2/token';
    }

    public function refreshAccessToken($grant = 'refresh_token', $params = array())
    {
        if (is_string($grant)) {
            $grant = 'Stamplia\\StampliaClient\\Grant\\'.ucfirst(str_replace('_', '', $grant));
            if ( ! class_exists($grant)) {
                throw new \InvalidArgumentException('Unknown grant "'.$grant.'"');
            }
            $grant = new $grant;
        } elseif ( ! $grant instanceof GrantInterface) {
            throw new \InvalidArgumentException($grant.' is not an instance of League\OAuth2\Client\Grant\GrantInterface');
        }

        $defaultParams = array(
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => $grant,
        );

        $requestParams = $grant->prepRequestParams($defaultParams, $params);

        try {
            switch ($this->method) {
                case 'get':
                    $client = new GuzzleClient($this->urlAccessToken() . '?' . http_build_query($requestParams,'','&'));
                    $client->setSslVerification(false, false, 0);
                    $request = $client->send();
                    $response = $request->getBody();
                    break;
                case 'post':
                    $client = new GuzzleClient($this->urlAccessToken());
                    $client->setSslVerification(false, false, 0);
                    $request = $client->post(null, null, $requestParams)->send();
                    $response = $request->getBody();
                    break;
            }
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $raw_response = explode("\n", $e->getResponse());
            $response = end($raw_response);
        }

        switch ($this->responseType) {
            case 'json':
                $result = json_decode($response, true);
                break;
            case 'string':
                parse_str($response, $result);
                break;
        }

        if (isset($result['error']) && ! empty($result['error'])) {
            throw new IDPException($result);
        }

        return $grant->handleResponse($result);
    }

    public function urlUserDetails(AccessToken $token)
    {
        try {

            $client = new GuzzleClient($this->domain.'/api/users/me.json?access_token='.$token);
            $request = $client->get()->send();
            $response = $request->getBody();
            $r = json_decode($response);
            return $this->domain.$r->_links->me->href.'.json?access_token='.$token;
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $raw_response = explode("\n", $e->getResponse());
            throw new IDPException(end($raw_response));

        }


    }

    public function getUrlauthorize() {
        return $this->urlAuthorize();
    }

    public function setDomain($domain) {
        $this->domain = $domain;
        return $this;
    }

    public function getUrlaccesstoken() {
        return $this->urlAccessToken();
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User;

        $user->uid = $response->user->id;
        $user->nickname = $response->user->slug;
        $user->name = $response->user->name;
        $user->email = $response->user->email;
        $user->location = $response->user->country;

        return $user;
    }
} 