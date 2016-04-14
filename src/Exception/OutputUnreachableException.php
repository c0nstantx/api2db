<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace Exception;
use Interfaces\OutputInterface;

/**
 * Description of OutputUnreachableException
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class OutputUnreachableException extends \Exception
{
    public function __construct(OutputInterface $output, \Exception $previous)
    {
        $message = sprintf("Output '%s' is unreachable. Please check your configuration and that the destination exists.", $output->getEndpoint());
        parent::__construct($message, 400, $previous);
    }
}