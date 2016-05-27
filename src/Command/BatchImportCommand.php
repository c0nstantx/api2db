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
use Model\InputService;
use Model\OutputService;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Description of BatchImportCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class BatchImportCommand extends Command
{
    const DEFAULT_MAX_PROCESSES = 5;
    
    /** @var InputService */
    protected $inputService;
    
    /** @var OutputService */
    protected $outputService;

    /** @var Logger */
    protected $logger;
    
    /** @var array */
    protected $inputs;
    
    /** @var array */
    protected $outputs;

    /** @var OutputInterface */
    protected $consoleOutput;

    /** @var int */
    protected $maxProcs;
    
    /** @var bool */
    protected $debug = false;

    /** @var bool */
    protected $disableNER = false;

    /** @var array */
    public static $activeProcesses = [];

    public function __construct(InputService $inputService, 
                                OutputService $outputService, 
                                Logger $logger, array $inputs, 
                                array $outputs
    )
    {
        parent::__construct();
        $this->inputService = $inputService;
        $this->outputService = $outputService;
        $this->logger = $logger;
        $this->inputs = $inputs;
        $this->outputs = $outputs;
    }

    protected function configure()
    {
        $this->setName("api2db:import:batch")
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Display and log debug information')
            ->addOption('max-procs', null, InputOption::VALUE_OPTIONAL, 'Define the maximum number of running processes', self::DEFAULT_MAX_PROCESSES)
            ->addOption('disable-ner', null, InputOption::VALUE_NONE, 'Disable NER parsing')
            ->setDescription("Import data from input endpoints");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeStart = microtime(true);
        $this->consoleOutput = $output;
        $this->debug = (bool)$input->getOption('debug');
        $this->maxProcs = (int)$input->getOption('max-procs');
        if ($this->maxProcs < 1) {
            $this->maxProcs = 1;
        }
        $this->disableNER = (bool)$input->getOption('disable-ner');
        $output->writeln("Start import...");
        try {
            /* Get all inputs */
            $inputs = $this->inputService->getInputs(array_keys($this->inputs));

            foreach($inputs as $input) {
                $this->parseInput($input);
            }
            $output->writeln("Data are imported!");
        } catch (\Exception $ex) {
            if ($this->debug) {
                $output->writeln("ERROR: ".$ex->getMessage());
            }
            $this->logger->critical($ex);
        }
        $output->writeln("Time elapsed: ".$this->getTimeElapsed($timeStart));
    }

    /**
     * @param \Interfaces\InputInterface $input
     */
    protected function parseInput(\Interfaces\InputInterface $input)
    {
        $inputName = $input->getName();
        $map = $this->inputService->getInputMap($inputName);
        if ($map && isset($map['imported'])) {
            foreach ($map['imported'] as $endpoint) {
                $this->waitProcesses($this->maxProcs - 1);
                self::$activeProcesses++;
                $command = 'php app/console.php api2db:import:endpoint '.$inputName
                    .' --endpoint "'. addslashes(json_encode($endpoint)).'"';
                if ($this->debug) {
                    $command .= ' --debug';
                }
                if ($this->disableNER) {
                    $command .= ' --disable-ner';
                }
                $process = new Process($command);
                $process->start();
                $pId = $process->getPid();
                self::$activeProcesses[$pId] = $process;
                if ($this->debug) {
                    $message = "$inputName: Reading '{$endpoint['url']}' (Process: $pId)";
                    $this->consoleOutput->writeln($message);
                    $this->logger->debug($message);
                }
            }
            $this->waitProcesses(0);
        }
    }

    /**
     * @param int $limit
     */
    protected function waitProcesses($limit)
    {
        while(count(self::$activeProcesses) > $limit) {
            foreach(self::$activeProcesses as $pId => $proc) {
                if ($proc->isSuccessful()) {
                    $this->consoleOutput->write($proc->getOutput());
                    unset(self::$activeProcesses[$pId]);
                    if ($this->debug) {
                        $message = "Process ($pId) finished";
                        $this->consoleOutput->writeln($message);
                        $this->logger->debug("$message\n");
                    }
                }
            }
        }

        return;
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