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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of NERInputParseCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class NERInputParseCommand extends BaseCommand
{
    /** @var InputService */
    protected $inputService;
    
    /** @var OutputService */
    protected $outputService;
    
    /** @var NERService */
    protected $NERService;

    /** @var array */
    protected $outputs;
    
    public function __construct(InputService $inputService, 
                                OutputService $outputService, 
                                NERService $NERService, Logger $logger, 
                                array $outputs
    )
    {
        parent::__construct($logger);
        $this->inputService = $inputService;
        $this->outputService = $outputService;
        $this->NERService = $NERService;
        $this->outputs = $outputs;
    }

    protected function configure()
    {
        $this->setName("api2db:ner:parse_input")
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Display and log debug information')
            ->addArgument('input', InputArgument::REQUIRED, 'The name of input (ex. twitter, facebook etc.)')
            ->setDescription("Parse single input data through NER");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->debug = (bool)$input->getOption('debug');

        try {
            $in = $this->inputService->getInput($input->getArgument('input'));
            $this->parseData($in);
        } catch (\Exception $ex) {
            if ($this->debug) {
                $output->writeln("ERROR: ".$ex->getMessage());
            }
            $this->logger->critical($ex);
        }
    }

    /**
     * @param \Interfaces\InputInterface $input
     */
    protected function parseData(\Interfaces\InputInterface $input)
    {
        $outputs = $this->outputService->getOutputs(array_keys($this->outputs));
        foreach($outputs as $output) {
            $data = $output->fetchData($input);
            if (is_array($data) && isset($data['owner'])) {
                foreach($data['owner'] as $key => $owner) {
                    $owner['map'] = $input->getDefaultMap();
                    $owner['id'] = $input->getDefaultId();
                    $reconstuctedData = $output->reconstructData($owner, $data['input'][$key]);
                    $entities = $this->NERService->getEntities($reconstuctedData);
                    $reconstuctedData->setEntities($entities);
                    $output->send($reconstuctedData);
                }
            }
        }
    }

}