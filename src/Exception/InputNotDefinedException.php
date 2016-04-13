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
 * Description of InputNotDefinedException
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class InputNotDefinedException extends \RuntimeException
{
    public function __construct($inputName, \Exception $previous = null)
    {
        $message = sprintf("Input '%s' was not defined in configuration", $inputName);
        parent::__construct($message, 400, $previous);
    }
}