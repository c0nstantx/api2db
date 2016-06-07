<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */

namespace Command;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;

/**
 * Description of BaseCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class BaseCommand extends Command
{
    /** @var Logger */
    protected $logger;

    /** @var bool */
    protected $debug = false;
    
    public function __construct(Logger $logger)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    /**
     * @param float $from
     *
     * @return string
     */
    protected function getTimeElapsed($from)
    {
        $seconds = round(microtime(true) - $from);

        $days = floor($seconds / (3600 * 24));
        $hours = floor($seconds / 3600 % 24);
        if ($hours < 10) {
            $hours = "0$hours";
        }
        $mins = floor($seconds / 60 % 60);
        if ($mins < 10) {
            $mins = "0$mins";
        }
        $secs = floor($seconds % 60);
        if ($secs < 10) {
            $secs = "0$secs";
        }

        return "$days days, $hours:$mins:$secs";
    }

}