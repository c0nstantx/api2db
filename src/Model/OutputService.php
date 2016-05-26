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
use Exception\OutputNotDefinedException;
use Exception\OutputNotFoundException;
use Interfaces\OutputInterface;

/**
 * Description of OutputService
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class OutputService
{
    const OUTPUT_NAMESPACE = '\\Output\\';

    const TIMEOUT_LIMIT = 2;
    
    protected static $outputMap = [
        'jena' => 'JenaOutput',
        'neo4j' => 'Neo4jOutput',
    ];

    /** @var array */
    protected $outputs = [];

    /** @var array */
    protected $outputOptions;

    public function __construct(array $outputOptions)
    {
        foreach($outputOptions as $name => $options) {
            if (!isset(self::$outputMap[$name])) {
                throw new OutputNotFoundException($name);
            }
        }
        $this->outputOptions = $outputOptions;
    }

    /**
     * @param OutputInterface $output
     * @param array $data
     * 
     * @return OutputData
     */
    public function getDataAdapter(OutputInterface $output, array $data)
    {
        switch ($output->getName()) {
            case 'jena':
            case 'neo4j':
            default:
                return new GraphOutputData($data['raw'], $data['endpoint']);
        }
    }
    
    /**
     * @param array $names
     *
     * @return array
     */
    public function getOutputs(array $names)
    {
        $outputs = [];

        foreach($names as $name) {
            $outputs[] = $this->getOutput($name);
        }

        return $outputs;
    }

    /**
     * @param string $name
     * 
     * @return OutputInterface
     */
    public function getOutput($name)
    {
        if (!isset($this->outputOptions[$name])) {
            throw new OutputNotDefinedException($name);
        }

        $className = self::$outputMap[$name];
        if (!isset($this->outputs[$name])) {
            $this->outputs[$name] = $this->buildOutput($className, $this->outputOptions[$name]);
        }

        return $this->outputs[$name];
    }

    /**
     * @param string $className
     * @param array $options
     * 
     * @return OutputInterface
     */
    protected function buildOutput($className, array $options)
    {
        $fullClass = self::OUTPUT_NAMESPACE.$className;

        return new $fullClass($options);
    }
}