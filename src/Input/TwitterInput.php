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
 * Description of TwitterInput
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class TwitterInput extends Oauth1Input
{
    /**
     * Get the URL for retrieving temporary credentials.
     *
     * @return string
     */
    public function urlTemporaryCredentials()
    {
        return 'https://api.twitter.com/oauth/request_token';
    }

    /**
     * Get the URL for redirecting the resource owner to authorize the client.
     *
     * @return string
     */
    public function urlAuthorization()
    {
        return 'https://api.twitter.com/oauth/authenticate';
    }

    /**
     * Get the URL retrieving token credentials.
     *
     * @return string
     */
    public function urlTokenCredentials()
    {
        return 'https://api.twitter.com/oauth/access_token';
    }
    
    public function getName()
    {
        return 'twitter';
    }
}