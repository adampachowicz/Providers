<?php

namespace SocialiteProviders\MailChimp;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'MAILCHIMP';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://login.mailchimp.com/oauth2/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://login.mailchimp.com/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://login.mailchimp.com/oauth2/metadata',
            [
                RequestOptions::HEADERS => [
                    'Accept'        => 'application/json',
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
            'id'       => $user['login']['login_id'],
            'nickname' => $user['login']['login_name'], 'name' => null,
            'email'    => $user['login']['login_email'], 'avatar' => null,
        ]);
    }
}
