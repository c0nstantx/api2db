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

/**
 * Description of BatchImportCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class BatchImportCommand extends Command
{
    protected $inputService;
    
    protected $outputService;
    
    protected $NERService;

    protected $logger;
    
    protected $inputs;
    
    protected $outputs;
    
    public function __construct(InputService $inputService, 
                                OutputService $outputService, 
                                NERService $NERService, Logger $logger,
                                array $inputs, array $outputs
    )
    {
        parent::__construct();
        $this->inputService = $inputService;
        $this->outputService = $outputService;
        $this->NERService = $NERService;
        $this->logger = $logger;
        $this->inputs = $inputs;
        $this->outputs = $outputs;
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

        /* Get all outputs */
        $outputs = $this->outputService->getOutputs(array_keys($this->outputs));

        foreach($inputs as $input) {
            $map = $this->inputService->getInputMap($input->getName());
            if ($map) {
                foreach ($map['imported'] as $endpoint) {
                    $rawData = $input->get($endpoint['url']);
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
                            var_dump($ex);
                            exit;
                            $this->logger->critical($ex);
                        }
                    }
                }
            }
        }
        $output->writeln("Data are imported!");
        $output->writeln("Time elapsed: ".(microtime(true) - $timeStart).' seconds');
    }

}