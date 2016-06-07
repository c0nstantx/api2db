<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */
namespace Input;

/**
 * Description of FacebookInput
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class FacebookInput extends Oauth2Input
{
    protected $tokenName = 'access_token';

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://www.facebook.com/v2.2/dialog/oauth';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params = [])
    {
        return 'https://graph.facebook.com/v2.2/oauth/access_token';
    }

    public function get($url, array $options = [], array $headers = [])
    {
        $result = parent::get($url, $options, $headers);

        if (isset($result['data'])) {
            return $result['data'];
        }
        
        return $result;
    }

    public function getName()
    {
        return 'facebook';
    }

    public function getType()
    {
        return 'facebook_post';
    }

    public function getDefaultMap()
    {
        return [
            'message'
        ];
    }

    public function getDefaultId()
    {
        return 'id';
    }
}