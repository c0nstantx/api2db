<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */

namespace Interfaces;
use GuzzleHttp\ClientInterface;
use Model\OutputData;

/**
 * Description of OutputInterface
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
interface OutputInterface
{
    /**
     * OutputInterface constructor.
     * 
     * @param array $options
     * @param ClientInterface|null $client
     */
    public function __construct(array $options, ClientInterface $client = null);
    
    /**
     * Send data to output
     * 
     * @param OutputData $data
     */
    public function send(OutputData $data);
}