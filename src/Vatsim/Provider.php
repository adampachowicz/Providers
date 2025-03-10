<?php

namespace SocialiteProviders\Vatsim;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'VATSIM';

    protected $scopeSeparator = ' ';

    /**
     * The scopes being requested that are mandatory.
     *
     * @var array
     */
    protected $requiredScopes = [];

    /**
     * @see https://github.com/vatsimnetwork/developer-info/wiki/Connect-Development-Environment
     *
     * @return string
     */
    protected function getHostname()
    {
        if ($this->getConfig('test')) {
            return 'auth-dev.vatsim.net';
        }

        return 'auth.vatsim.net';
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://'.$this->getHostname().'/oauth/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://'.$this->getHostname().'/oauth/token';
    }

    /**
     * {@inheritDoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://'.$this->getHostname().'/api/user',
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritDoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'cid'          => Arr::get($user, 'data.cid'),
            'first_name'   => Arr::get($user, 'data.personal.name_first'),
            'last_name'    => Arr::get($user, 'data.personal.name_last'),
            'full_name'    => Arr::get($user, 'data.personal.name_full'),
            'rating'       => Arr::get($user, 'data.vatsim.rating.id'),
            'pilot_rating' => Arr::get($user, 'data.vatsim.pilotrating.id'),
            'region'       => Arr::get($user, 'data.vatsim.region.id'),
            'division'     => Arr::get($user, 'data.vatsim.division.id'),
            'subdivision'  => Arr::get($user, 'data.vatsim.subdivision.id'),

        ]);
    }

    public static function additionalConfigKeys(): array
    {
        return ['test'];
    }

    /**
     * {@inheritDoc}
     */
    public function getScopes()
    {
        return array_unique(array_merge(parent::getScopes(), $this->getRequiredScopes()));
    }

    /**
     * {@inheritDoc}
     */
    protected function getCodeFields($state = null)
    {
        $fields = parent::getCodeFields($state);

        if ($requiredScopes = $this->getRequiredScopes()) {
            $fields['required_scopes'] = $this->formatScopes($requiredScopes, $this->scopeSeparator);
        }

        return $fields;
    }

    /**
     * Merge the required scopes of the requested access.
     *
     * @param  array|string  $scopes
     * @return $this
     */
    public function requiredScopes($scopes)
    {
        $this->requiredScopes = array_unique(array_merge($this->requiredScopes, (array) $scopes));

        return $this;
    }

    /**
     * Set the required scopes of the requested access.
     *
     * @param  array|string  $scopes
     * @return $this
     */
    public function setRequiredScopes($scopes)
    {
        $this->requiredScopes = array_unique((array) $scopes);

        return $this;
    }

    /**
     * Get the current required scopes.
     *
     * @return array
     */
    public function getRequiredScopes()
    {
        return $this->requiredScopes;
    }
}
