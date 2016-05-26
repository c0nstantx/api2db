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
 * Description of ImporterInterface
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
interface ImporterInterface
{
    public function import(array $names);
}