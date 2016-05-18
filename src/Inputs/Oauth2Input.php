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
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Model\ConnectorHelper;
use Model\InputService;
use Psr\Http\Message\ResponseInterface;

/**
 * Description of Oauth2Input
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
abstract class Oauth2Input extends AbstractProvider implements InputInterface
{
    /** @var Client */
    protected $client;

    /** @var AccessToken */
    protected $token;

    /** @var string */
    protected $tokenName = null;

    public function __construct(array $credentials)
    {
        parent::__construct();

        if (isset($credentials['access_token'])) {
            $this->token = new AccessToken($credentials);
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
        if ($this->tokenName) {
            $options[$this->tokenName] = $this->token->getToken();
        }
        $url = ConnectorHelper::bindUrlOptions($url, $options);
        $requestHeaders = $this->buildHeaders($headers);

        $requestOptions = [
            'headers' => $requestHeaders,
            'connect_timeout' => InputService::TIMEOUT_LIMIT
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
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        // TODO: Implement getResourceOwnerDetailsUrl() method.
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * Checks a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array|string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        // TODO: Implement checkResponse() method.
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param  array $response
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        // TODO: Implement createResourceOwner() method.
    }

    protected function buildHeaders(array $extraHeaders = [])
    {
        $headers = $this->getHeaders($this->token);

        return array_merge($headers, $extraHeaders);
    }
}