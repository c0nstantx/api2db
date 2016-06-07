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
use Interfaces\OutputInterface;

/**
 * Description of OutputUnreachableException
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class OutputUnreachableException extends \Exception
{
    public function __construct(OutputInterface $output, \Exception $previous)
    {
        $message = sprintf("Output '%s' is unreachable. Please check your configuration and that the destination exists.", $output->getName());
        parent::__construct($message, 400, $previous);
    }
}