<?php
/**
 * Stamplia.php
 *
 * Created By: jonathan
 * Date: 20/01/14
 * Time: 11:38
 */

namespace Kiwup\StampliaClient\Provider;

use League\OAuth2\Client\Provider\IdentityProvider;
use League\OAuth2\Client\Provider\User;
use League\OAuth2\Client\Token\AccessToken;
use Guzzle\Service\Client as GuzzleClient;
use League\OAuth2\Client\Exception\IDPException as IDPException;

class Stamplia extends IdentityProvider{

    public function urlAuthorize()
    {
        return 'https://stamplia.com/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://stamplia.com/oauth/v2/token';
    }

    public function urlUserDetails(AccessToken $token)
    {
        try {

            $client = new GuzzleClient('https://stamplia.com/api/users/me.json?access_token='.$token);
            $request = $client->get()->send();
            $response = $request->getBody();
            $r = json_decode($response);
            return 'https://stamplia.com'.$r->_links->me->href.'.json?access_token='.$token;
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            $raw_response = explode("\n", $e->getResponse());
            throw new IDPException(end($raw_response));

        }


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