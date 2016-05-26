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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Description of ImportNamesCommand
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class ImportNamesCommand extends Command
{
    protected $importerService;
    
    public function __construct(ImporterService $importerService)
    {
        parent::__construct();
        $this->importerService = $importerService;
    }

    protected function configure()
    {
        $this->setName("api2db:import:names")
            ->addArgument('file', InputArgument::REQUIRED, 'The file location with a list of names (one name per line)')
            ->setDescription("Import names to input endpoints");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timeStart = microtime(true);
        $file = $input->getArgument('file');
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }

        $names = $this->parseFile($file);
        try {
            $this->importerService->importNames($names);
            $output->writeln(count($names)." name(s) are imported!");
        } catch (\Exception $ex) {
            var_dump($ex);
            exit;
        }
        $output->writeln("Time elapsed: ".(microtime(true) - $timeStart).' seconds');
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