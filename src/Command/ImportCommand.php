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
    protected $inputService;
    
    protected $outputService;
    
    protected $NERService;

    protected $logger;
    
    protected $outputs;
    
    protected $debug;
    
    public function __construct(InputService $inputService, 
                                OutputService $outputService, 
                                NERService $NERService, Logger $logger, 
                                array $outputs, $debug = false
    )
    {
        parent::__construct();
        $this->inputService = $inputService;
        $this->outputService = $outputService;
        $this->NERService = $NERService;
        $this->logger = $logger;
        $this->outputs = $outputs;
        $this->debug = $debug;
    }

    protected function configure()
    {
        $this->setName("api2db:import:endpoint")
            ->addOption('endpoint', null, InputOption::VALUE_REQUIRED, 'The requested endpoint data as JSON string')
            ->addArgument('input', InputArgument::REQUIRED, 'The name of input (ex. twitter, facebook etc.)')
            ->setDescription("Import names to input endpoints");
        
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
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
            try {
                $data = $this->outputService->getDataAdapter($output, $inputData);
                $entities = $this->NERService->getEntities($data);
                $data->setEntities($entities);
                $output->send($data);
            } catch (\Exception $ex) {
                $this->logger->critical($ex);
            }
        }
        
    }
}