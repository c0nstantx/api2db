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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Description of ImportNamesCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class ImportNamesCommand extends BaseCommand
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
        $this->setName("api2db:import:names")
            ->addOption('update', null, InputOption::VALUE_NONE, 'Update the imported endpoints if the same is found')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Display and log debug information')
            ->addArgument('file', InputArgument::REQUIRED, 'The file location with a list of names (one name per line)')
            ->setDescription("Import names to input endpoints");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->debug = (bool)$input->getOption('debug');
        $timeStart = microtime(true);

        try {
            $file = $input->getArgument('file');
            if (!file_exists($file)) {
                throw new FileNotFoundException($file);
            }
            $names = $this->parseFile($file);
            $update = (bool)$input->getOption('update');
            $this->importerService->importNames($names, $update);
            $output->writeln(count($names)." name(s) are imported!");
        } catch (\Exception $ex) {
            if ($this->debug) {
                $output->writeln("ERROR: ".$ex->getMessage());
            }
            $this->logger->critical($ex);
        }
        $output->writeln("Time elapsed: ".$this->getTimeElapsed($timeStart));
    }

    /**
     * @param string $filename
     * 
     * @return array
     */
    protected function parseFile($filename)
    {
        $names = [];
        $fp = fopen($filename, 'rb');
        if ($fp) {
            while($line = fgets($fp)) {
                $names[] = $line;
            }
            fclose($fp);
            return $names;
        } else {
            throw new \RuntimeException("Error reading '$filename'. Please check that the file exists and you have the proper privileges.");
        }
    }
}