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
        $output->writeln("Time elapsed: ".(microtime(true) - $timeStart).' seconds');
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
                while(count(self::$activeProcesses) >= $this->maxProcs) {
                    foreach(self::$activeProcesses as $pId => $proc) {
                        if ($proc->isSuccessful()) {
                            $this->consoleOutput->write($proc->getOutput());
                            unset(self::$activeProcesses[$pId]);
                            $this->logger->debug("Process ($pId) finished\n");
                        }
                    }
                }
                self::$activeProcesses++;
                if ($this->debug) {
                    $this->consoleOutput->writeln("$inputName: Reading '{$endpoint['url']}'");
                    $this->logger->debug("$inputName: Reading '{$endpoint['url']}'");
                }
                $command = 'php app/console.php api2db:import:endpoint '.$inputName.' --endpoint "'. addslashes(json_encode($endpoint)).'"';
                if ($this->debug) {
                    $command .= ' --debug';
                }
                $process = new Process($command);
                $process->start();
                self::$activeProcesses[$process->getPid()] = $process;
            }
        }
    }

}