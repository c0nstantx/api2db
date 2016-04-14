<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */
namespace Inputs;

/**
 * Description of InstagramInput
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class InstagramInput extends Oauth2Input
{

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://api.instagram.com/oauth/authorize';
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
        return 'https://api.instagram.com/oauth/access_token';
    }

    public function getName()
    {
        return 'instagram';
    }
}