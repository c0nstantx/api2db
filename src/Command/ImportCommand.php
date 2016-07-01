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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of ImportCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class ImportCommand extends Command
{
    /** @var InputService */
    protected $inputService;
    
    /** @var OutputService */
    protected $outputService;
    
    /** @var NERService */
    protected $NERService;

    /** @var Logger */
    protected $logger;
    
    /** @var array */
    protected $outputs;

    /** @var bool */
    protected $debug = false;
    
    public function __construct(InputService $inputService, 
                                OutputService $outputService, 
                                NERService $NERService, Logger $logger, 
                                array $outputs
    )
    {
        parent::__construct();
        $this->inputService = $inputService;
        $this->outputService = $outputService;
        $this->NERService = $NERService;
        $this->logger = $logger;
        $this->outputs = $outputs;
    }

    protected function configure()
    {
        $this->setName("api2db:import:endpoint")
            ->addOption('endpoint', null, InputOption::VALUE_REQUIRED, 'The requested endpoint data as JSON string')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Display and log debug information')
            ->addOption('disable-ner', null, InputOption::VALUE_NONE, 'Disable NER parsing')
            ->addArgument('input', InputArgument::REQUIRED, 'The name of input (ex. twitter, facebook etc.)')
            ->setDescription("Import data from a single input endpoint ");
        
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $consoleInput, OutputInterface $consoleOutput)
    {
        $this->debug = (bool)$consoleInput->getOption('debug');
        try {
            /* Get all outputs */
            $outputs = $this->outputService->getOutputs(array_keys($this->outputs));


            $inputName = $consoleInput->getArgument('input');
            $endpoint = json_decode($consoleInput->getOption('endpoint'), true);

            $inputDriver = $this->inputService->getInput($inputName);
            $rawData = $inputDriver->get($endpoint['url'], [], []);
            $inputData = [
                'raw' => $rawData,
                'endpoint' => $endpoint
            ];

            $disableNER = (bool)$consoleInput->getOption('disable-ner');
            foreach($outputs as $output) {
                $data = $this->outputService->getDataAdapter($output, $inputData);
                if (!$disableNER) {
                    $entities = $this->NERService->getEntities($data);
                    $data->setEntities($entities);
                }
                $output->send($data);
            }
        } catch (\Exception $ex) {
            if ($this->debug) {
                $consoleOutput->writeln("ERROR: ".$ex->getMessage());
            }
            $this->logger->critical($ex);
        }
    }
}