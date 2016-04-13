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
 * Description of StorageInterface
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
interface StorageInterface
{
    /**
     * @param mixed $data
     * @param string|null $domain
     */
    public function save($data, $domain = null);

    /**
     * @param string $key
     * 
     * @return mixed
     */
    public function find($domain = 'default');
}