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
use Model\NERService;
use Model\OutputService;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Description of NERParseCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class NERParseCommand extends MultiThreadCommand
{
    const DEFAULT_MAX_PROCESSES = 5;

    /** @var NERService */
    protected $NERService;

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

    /** @var bool */
    protected $debug;

    /** @var int */
    protected $maxProcs;

    public function __construct(NERService $NERService, InputService $inputService,
                                OutputService $outputService, Logger $logger,
                                array $inputs, array $outputs, $procPath
    )
    {
        parent::__construct($logger, $procPath);
        $this->NERService = $NERService;
        $this->inputService = $inputService;
        $this->outputService = $outputService;
        $this->logger = $logger;
        $this->inputs = $inputs;
        $this->outputs = $outputs;
    }

    protected function configure()
    {
        $this->setName("api2db:ner:parse")
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Display and log debug information')
            ->addOption('max-procs', null, InputOption::VALUE_OPTIONAL, 'Define the maximum number of running processes', self::DEFAULT_MAX_PROCESSES)
            ->setDescription("Parse data through NER");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->debug = (bool)$input->getOption('debug');
        $this->maxProcs = (int)$input->getOption('max-procs');

        $timeStart = microtime(true);
        try {
            /* Get all inputs */
            $inputs = $this->inputService->getInputs(array_keys($this->inputs));

            $this->parseInputs($inputs, $output);
            $output->writeln("Data are imported!");
            $output->writeln("Time elapsed: ".$this->getTimeElapsed($timeStart));
        } catch (\Exception $ex) {
            if ($this->debug) {
                $output->writeln("ERROR: ".$ex->getMessage());
            }
            $this->logger->critical($ex);
        }
    }

    protected function parseInputs(array $inputs, OutputInterface $output)
    {
        foreach($inputs as $input) {
            $this->waitProcesses($this->maxProcs - 1);
            self::$activeProcesses++;
            $inputName = $input->getName();
            $command = 'php app/console.php api2db:ner:parse_input '.$inputName;
            if ($this->debug) {
                $command .= ' --debug';
            }
            $process = new Process($command);
            $process->start();
            $pId = $process->getPid();
            self::$activeProcesses[$pId] = $process;
            if ($this->debug) {
                $message = "$inputName: Parse NER (Process: $pId)";
                $output->writeln($message);
                $this->logger->debug($message);
            }
        }
        $this->waitProcesses(0);
    }
}