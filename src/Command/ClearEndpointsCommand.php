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
use Model\ImporterService;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Description of ClearEndpointsCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class ClearEndpointsCommand extends BaseCommand
{
    /** @var ImporterService */
    protected $importerService;
    
    public function __construct(ImporterService $importerService, Logger $logger)
    {
        parent::__construct($logger);
        $this->importerService = $importerService;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this->setName("api2db:clear:endpoints")
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Display and log debug information')
            ->setDescription("Clear the imported endpoints");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you wish to continue? (y/n)', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }
        
        $this->debug = (bool)$input->getOption('debug');
        try {
            $this->importerService->clearNames();
            $output->writeln("Endpoints cleared");
        } catch (\Exception $ex) {
            if ($this->debug) {
                $output->writeln("ERROR: ".$ex->getMessage());
            }
            $this->logger->critical($ex);
        }
    }
}