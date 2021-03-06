<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */
namespace Exception;

/**
 * Description of OutputNotFoundException
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class OutputNotFoundException extends \RuntimeException
{
    public function __construct($outputName, \Exception $previous = null)
    {
        $message = sprintf("Output '%s' was not found", $outputName);
        parent::__construct($message, 400, $previous);
    }
}