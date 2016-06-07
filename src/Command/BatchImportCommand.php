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
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Description of BatchImportCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class BatchImportCommand extends MultiThreadCommand
{
    /** @var InputService */
    protected $inputService;
    
    /** @var array */
    protected $inputs;

    /** @var OutputInterface */
    protected $consoleOutput;

    /** @var bool */
    protected $disableNER = false;

    public function __construct(InputService $inputService, Logger $logger, 
                                array $inputs, $procPath
    )
    {
        parent::__construct($logger, $procPath);
        $this->inputService = $inputService;
        $this->logger = $logger;
        $this->inputs = $inputs;
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
        parent::execute($input, $output);
        if (!$this->proceedProcess($input, $output)) {
            return 1;
        }
        $myPid = getmypid();
        $this->saveProcess($myPid);

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
        $this->clearProcess($myPid);
        $output->writeln("Time elapsed: ".$this->getTimeElapsed($timeStart));
        
        return true;
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
}