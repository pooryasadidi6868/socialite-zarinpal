<?php

namespace PooryaSadidi\ZarinPal;

use Illuminate\Support\Arr;
use Predis\ClientException;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    public const IDENTIFIER = 'ZARINPAL';

    /**
     * {@inheritdoc}
     */
    protected $scopes = ['user.read'];

    /**
     * {@inherticdoc}.
     */
    protected $scopeSeparator = ' ';


    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://next.zarinpal.com/api/oauth/authorizeInit', $state);
    }


//    protected function buildAuthUrlFromBase($url, $state)
//    {
//        return $url.'?'.http_build_query($this->getCodeFields($state));
//    }

    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => $this->formatScopes($this->getScopes(), $this->scopeSeparator),
            'response_type' => 'code',
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }
        $verifier = ($this->isStateless()) ? \Illuminate\Support\Str::random(128) : $this->request->session()->pull('code_verifier');
        $fields['code_challenge'] = $this->getCodeChallenge($verifier);
        $fields['code_challenge_method'] = $this->getCodeChallengeMethod();
        $fields['state'] = $verifier;

        return array_merge($fields, $this->parameters);
    }


    protected function getCodeChallenge($verifier)
    {

        $hashed = hash('sha256', $verifier, true);
        return rtrim(strtr(base64_encode($hashed), '+/', '-_'), '='); // base64-URL-encoding
    }

    protected function getCodeChallengeMethod()
    {
        return 'S256';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://next.zarinpal.com/api/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $graphQLquery = include('Query.php');
        $response = $this->getHttpClient()->post('https://next.zarinpal.com/api/v4/graphql',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'form_params' => $graphQLquery
            ]);



        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $user = $user['data']['Me'];
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'phone' => $user['cell_number'],
            'avatar' => $user['avatar'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'code_verifier' => request()->input('state'),
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $this->getTokenFields($code),
        ]);

        return json_decode($response->getBody(), true);
    }

}
