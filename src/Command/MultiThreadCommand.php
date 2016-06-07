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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

/**
 * Description of MultiThreadCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class MultiThreadCommand extends BaseCommand
{
    const DEFAULT_MAX_PROCESSES = 5;

    /** @var string */
    protected $procPath;

    /** @var bool */
    protected $debug = false;

    /** @var int */
    protected $maxProcs;
    
    /** @var OutputInterface */
    private $output;
    
    /** @var array */
    public static $activeProcesses = [];

    public function __construct(Logger $logger, $procPath)
    {
        parent::__construct($logger);
        $this->procPath = $procPath;
        $this->maxProcs = self::DEFAULT_MAX_PROCESSES;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    protected function proceedProcess(InputInterface $input, OutputInterface $output)
    {
        $procFile = $this->procPath.'/pid';
        if (file_exists($procFile)) {
            $running = [];
            $procs = explode("\n", trim(file_get_contents($procFile)));
            foreach($procs as $proc) {
                $subProc = new Process("ps -p $proc -o comm=");
                $subProc->run(function($type, $buffer) use ($proc, &$running) {
                    if ($type === Process::OUT) {
                        $running[] = (int)$proc;
                    }
                });
            }

            $stopped = array_diff($procs, $running);
            foreach($stopped as $stop) {
                $this->clearProcess($stop);
            }
            if (count($running)) {
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion('There are current process running. Are you sure you wish to continue? (y/n)', false);

                if (!$helper->ask($input, $output, $question)) {
                    return false;
                }
                return true;
            }
        }

        return true;
    }

    /**
     * @param int $limit
     */
    protected function waitProcesses($limit)
    {
        while(count(self::$activeProcesses) > $limit) {
            foreach(self::$activeProcesses as $pId => $proc) {
                if ($proc->isSuccessful()) {
                    $this->output->write($proc->getOutput());
                    unset(self::$activeProcesses[$pId]);
                    if ($this->debug) {
                        $message = "Process ($pId) finished";
                        $this->output->writeln($message);
                        $this->logger->debug("$message\n");
                    }
                }
            }
        }

        return;
    }

    /**
     * @param int $pId
     */
    protected function saveProcess($pId)
    {
        $procFile = $this->procPath.'/pid';
        if (file_exists($procFile)) {
            $procs = file_get_contents($procFile);
            file_put_contents($procFile, "$procs\n$pId");
        } else {
            file_put_contents($procFile, $pId);
        }
    }

    /**
     * @param int $pId
     */
    protected function clearProcess($pId)
    {
        $procFile = $this->procPath.'/pid';
        if (file_exists($procFile)) {
            $procs = explode("\n", trim(file_get_contents($procFile)));
            if ($key = array_search($pId, $procs)) {
                unset($procs[$key]);
            }
            file_put_contents($procFile, implode("\n", $procs));
        }
    }
}