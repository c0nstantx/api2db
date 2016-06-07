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

/**
 * Description of InputInterface
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
interface InputInterface
{
    /**
     * InputInterface constructor.
     * 
     * @param array $credentials
     */
    public function __construct(array $credentials);

    /**
     * Get a response from input
     * 
     * @param string $url
     * @param array $options
     * @param array $headers
     * 
     * @return mixed
     */
    public function get($url, array $options, array $headers);

    /**
     * Returns input name
     * 
     * @return string
     */
    public function getName();

    /**
     * Returns input type
     * 
     * @return string
     */
    public function getType();

    /**
     * Returns default input map
     * 
     * @return mixed
     */
    public function getDefaultMap();

    /**
     * Returns default input identifier
     * 
     * @return string
     */
    public function getDefaultId();
}