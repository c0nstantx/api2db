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
            ->addArgument('input', InputArgument::REQUIRED, 'The name of input (ex. twitter, facebook etc.)')
            ->setDescription("Import names to input endpoints");
        
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->debug = (bool)$input->getOption('debug');
        try {
            /* Get all outputs */
            $outputs = $this->outputService->getOutputs(array_keys($this->outputs));
    
            
            $inputName = $input->getArgument('input');
            $endpoint = json_decode($input->getOption('endpoint'), true);
    
            $inputDriver = $this->inputService->getInput($inputName);
            $rawData = $inputDriver->get($endpoint['url'], [], []);
            $inputData = [
                'raw' => $rawData,
                'endpoint' => $endpoint
            ];
    
            foreach($outputs as $output) {
                $data = $this->outputService->getDataAdapter($output, $inputData);
                $entities = $this->NERService->getEntities($data);
                $data->setEntities($entities);
                $output->send($data);
            }
        } catch (\Exception $ex) {
            if ($this->debug) {
                $output->writeln("ERROR: ".$ex->getMessage());
            }
            $this->logger->critical($ex);
        }
    }
}