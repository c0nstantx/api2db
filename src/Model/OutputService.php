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
use Symfony\Component\HttpFoundation\Request;

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
     * @param Request $request
     *
     * @return array
     */
    public function getDataFromRequest(Request $request)
    {
        $data = [
            'name' => $request->get('output_name'),
            'host' => $request->get('output_host'),
            'port' => $request->get('output_port'),
        ];
        if ($request->get('output_id') === 'neo4j') {
            $data['credentials'] = [
                'username' => $request->get('output_username'),
                'password' => $request->get('output_password'),
            ];
        }

        if ($request->get('output_id') === 'jena') {
            $data['path'] = $request->get('input_path');
        }

        return $data;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function validate(Request $request)
    {
        $required = [
            'output_id',
            'output_name',
            'output_host',
            'output_port',
        ];
        $jena = [
            'output_path',
        ];
        $neo4j = [
            'output_username',
            'output_password',
        ];

        $errors = [];

        if ($request->get('output_id') === 'jena') {
            $required = array_merge($required, $jena);
        } else if ($request->get('output_id') === 'neo4j') {
            $required = array_merge($required, $neo4j);
        }

        foreach($required as $req) {
            if (!$request->get($req) || $request->get($req) === '') {
                $errors[] = $req;
            }
        }

        return $errors;
    }

    /**
     * @return array
     */
    public function getAvailableOutputs()
    {
        return array_keys(self::$outputMap);
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