<?php

namespace SocialiteProviders\SoundCloud;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'SOUNDCLOUD';

    protected $scopes = ['non-expiring'];

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://soundcloud.com/connect', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://api.soundcloud.com/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://api.soundcloud.com/me.json',
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'OAuth '.$token,
                ],
            ]
        );

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'   => $user['id'], 'nickname' => $user['username'],
            'name' => null, 'email' => null, 'avatar' => $user['avatar_url'],
        ]);
    }
}
