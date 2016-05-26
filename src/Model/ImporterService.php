<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */

namespace Model;

/**
 * Description of ImporterService
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class ImporterService
{
    const IMPORTER_NAMESPACE = '\\Importer\\';

    protected $inputService;

    protected $inputs;

    protected $importers;

    public function __construct(InputService $inputService, array $inputs)
    {
        $this->inputService = $inputService;
        $this->importers = $this->buildImporters(array_keys($inputs));
    }

    public function importNames(array $names)
    {
        foreach($this->importers as $importer) {
            $importer->import($names);
        }
    }
    
    /**
     * @param array $inputs
     *
     * @return array
     */
    protected function buildImporters(array $inputs)
    {
        $importers = [];
        foreach($inputs as $input) {
            $importerClassName = ucfirst(strtolower($input)).'Importer';
            $fullClass = self::IMPORTER_NAMESPACE.$importerClassName;
            try {
                $importers[$input] = new $fullClass($this->inputService);
            } catch (\Exception $ex) {
                var_dump($ex);
            } catch (\Error $err) {
                var_dump($err);
            }
        }

        return $importers;
    }
}