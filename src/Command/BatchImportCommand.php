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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Description of BatchImportCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class BatchImportCommand extends Command
{
    const MAX_PROCESSES = 5;
    
    protected $inputService;
    
    protected $outputService;
    
    protected $NERService;

    protected $logger;
    
    protected $inputs;
    
    protected $outputs;
    
    protected $debug;

    public static $activeProcesses = [];
    
    public function __construct(InputService $inputService, 
                                OutputService $outputService, 
                                NERService $NERService, Logger $logger,
                                array $inputs, array $outputs, $debug = false
    )
    {
        parent::__construct();
        $this->inputService = $inputService;
        $this->outputService = $outputService;
        $this->NERService = $NERService;
        $this->logger = $logger;
        $this->inputs = $inputs;
        $this->outputs = $outputs;
        $this->debug = $debug;
    }

    protected function configure()
    {
        $this->setName("api2db:import:batch")
            ->setDescription("Import data from input endpoints");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeStart = microtime(true);
        $output->writeln("Start import...");

        /* Get all inputs */
        $inputs = $this->inputService->getInputs(array_keys($this->inputs));

        foreach($inputs as $input) {
            $inputName = $input->getName();
            $map = $this->inputService->getInputMap($inputName);
            if ($map && isset($map['imported'])) {
                foreach ($map['imported'] as $endpoint) {
                    while(count(self::$activeProcesses) >= self::MAX_PROCESSES) {
                        foreach(self::$activeProcesses as $pId => $proc) {
                            if ($proc->isSuccessful()) {
                                unset(self::$activeProcesses[$pId]);
                                $this->logger->debug("Process ($pId) finished\n");
                            }
                        }
                    }
                    self::$activeProcesses++;
                    if ($this->debug) {
                        $output->writeln("$inputName: Reading '{$endpoint['url']}'");
                        $this->logger->debug("$inputName: Reading '{$endpoint['url']}'");
                    }
                    $command = 'php app/console.php api2db:import:endpoint '.$inputName.' --endpoint "'. addslashes(json_encode($endpoint)).'"';
                    $process = new Process($command);
                    $process->start();
                    self::$activeProcesses[$process->getPid()] = $process;
                }
            }
        }
        $output->writeln("Data are imported!");
        $output->writeln("Time elapsed: ".(microtime(true) - $timeStart).' seconds');
    }

}