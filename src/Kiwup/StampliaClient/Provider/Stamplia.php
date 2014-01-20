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

            $client = new GuzzleClient('https://stamplia.com/api/users/me?access_token='.$token);
            $request = $client->get()->send();
            $response = $request->getBody();

        } catch (\Guzzle\Http\Exception\BadResponseException $e) {

            $raw_response = explode("\n", $e->getResponse());
            throw new IDPException(end($raw_response));

        }
        $r = json_decode($response);
        return 'https://stamplia.com/api'.$r->data->_links->me->href.'?access_token='.$token;
    }

    public function userDetails($response, AccessToken $token)
    {



        $user = new User;

        $user->uid = $response->data->id;
        $user->nickname = $response->data->username;
        $user->name = $response->data->full_name;
        $user->description = isset($response->data->bio) ? $response->data->bio : null;
        $user->imageUrl = $response->data->profile_picture;

        return $user;
    }
} 