<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */
namespace Outputs;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Interfaces\OutputInterface;
use Model\OutputData;

/**
 * Description of AbstractOutput
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
abstract class AbstractOutput implements OutputInterface
{
    protected $client;

    /** @var string */
    protected $outputEndpoint;

    /** @var array */
    protected $config;
    
    /**
     * OutputInterface constructor.
     *
     * @param array $options
     */
    public function __construct(array $options, ClientInterface $client = null)
    {
        if (null === $client) {
            $this->client = new Client();
        } else {
            $this->client = $client;
        }
        
        $this->setup($options);

        $this->parseExtraOptions($options);
        $this->checkOutput();
    }

    /**
     * Setup output
     * 
     * @param array $options
     */
    protected function setup(array $options)
    {
        $this->config = [
            'schema' => isset($options['schema']) ? $options['schema'] : 'http',
            'host' => $options['host'],
            'port' => isset($options['port']) ? $options['port'] : null,
            'path' => isset($options['path']) ? $options['path'] : null
        ];
        $this->outputEndpoint = sprintf("%s://%s", $this->config['schema'], $this->config['host']);
        if ($this->config['port']) {
            $this->outputEndpoint .= ":{$this->config['port']}";
        }
        if ($this->config['path']) {
            $this->outputEndpoint .= "/{$this->config['path']}";
        }
    }
    
    /**
     * Check if output is accessible
     */
    protected function checkOutput()
    {
        $this->client->request('GET', $this->outputEndpoint);
    }

    /**
     * @param array $options
     */
    protected function parseExtraOptions(array $options)
    {
        /** Override in child classes */
    }
    
    /**
     * Send data to output
     *
     * @param mixed $data
     */
    abstract public function send(OutputData $data);
}