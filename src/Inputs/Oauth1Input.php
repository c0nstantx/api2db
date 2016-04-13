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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Interfaces\InputInterface;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Server\User;
use Model\ConnectorHelper;

/**
 * Description of Oauth1Input
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
abstract class Oauth1Input extends Server implements InputInterface
{
    /** @var Client */
    protected $client;
    
    /** @var TokenCredentials */
    protected $token;

    /** @var string */
    protected $id;

    /** @var string */
    protected $secret;
    
    public function __construct(array $credentials)
    {
        parent::__construct($credentials);

        $this->id = $credentials['identifier'];
        $this->secret = $credentials['secret'];
        
        if (isset($credentials['client_secret']) && isset($credentials['client_key'])) {
            $this->token = new TokenCredentials();
            $this->token->setSecret($credentials['client_secret']);
            $this->token->setIdentifier($credentials['client_key']);
        }
        
        $this->client = new Client();
    }

    /**
     * Get a response from input
     *
     * @param string $url
     * @param array $options
     * @param array $headers
     *
     * @return mixed
     */
    public function get($url, array $options = [], array $headers = [])
    {
        if ($this->needsAuthQuery()) {
            $baseUrl = ConnectorHelper::bindUrlOptions($url, $options);
            $requestHeaders = $this->buildHeaders($baseUrl, $headers);
            $options = array_merge($options, $this->getAuthParams($baseUrl));

            $url = ConnectorHelper::bindUrlOptions($url, $options);
        } else {
            $url = ConnectorHelper::bindUrlOptions($url, $options);
            $requestHeaders = $this->buildHeaders($url, $headers);
        }
        $requestOptions = [
            'headers' => $requestHeaders
        ];

        try {
            $response = $this->client->get($url, $requestOptions);
        } catch (ClientException $ex) {
            $response = $ex->getResponse();
        }

        $body = (string)$response->getBody();

        return json_decode($body, true);
    }
    
    /**
     * Get the URL for retrieving user details.
     *
     * @return string
     */
    public function urlUserDetails()
    {
        return null;
    }

    /**
     * Take the decoded data from the user details URL and convert
     * it to a User object.
     *
     * @param mixed $data
     * @param TokenCredentials $tokenCredentials
     *
     * @return User
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        return null;
    }

    /**
     * Take the decoded data from the user details URL and extract
     * the user's UID.
     *
     * @param mixed $data
     * @param TokenCredentials $tokenCredentials
     *
     * @return string|int
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        // TODO: Implement userUid() method.
    }

    /**
     * Take the decoded data from the user details URL and extract
     * the user's email.
     *
     * @param mixed $data
     * @param TokenCredentials $tokenCredentials
     *
     * @return string
     */
    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return null;
    }

    /**
     * Take the decoded data from the user details URL and extract
     * the user's screen name.
     *
     * @param mixed $data
     * @param TokenCredentials $tokenCredentials
     *
     * @return string
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return null;
    }

    /**
     * Build header for the given URL.
     *
     * @param string $url
     *
     * @return array
     */
    protected function buildHeaders($url, $extraHeaders = [])
    {
        $header = $this->protocolHeader('GET', $url, $this->token);
        $authorizationHeader = array('Authorization' => $header);

        $headers = $this->buildHttpClientHeaders($authorizationHeader);

        return array_merge($headers, $extraHeaders);
    }

    /**
     * Return auth parameters as array
     *
     * @param string $url
     *
     * @return array
     */
    protected function getAuthParams($url)
    {
        $authString = $this->protocolHeader('GET', $url, $this->token);

        $sanitizedString = trim(trim($authString, 'OAuth'));
        $options = explode(', ', $sanitizedString);

        $authParams = [];
        foreach($options as $option) {
            $parts = explode("=", $option);
            $authParams[$parts[0]] = trim($parts[1], '"');
        }

        return $authParams;
    }

    /**
     * Returns if the input needs the authentication string as an http query.
     * Override inside extended inputs whenever is needed
     *
     * @return bool
     */
    protected function needsAuthQuery()
    {
        return false;
    }
}