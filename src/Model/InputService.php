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
use Exception\InputNotDefinedException;
use Exception\InputNotFoundException;
use Interfaces\InputInterface;
use Interfaces\StorageInterface;

/**
 * Description of InputService
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class InputService
{
    const INPUT_NAMESPACE = '\\Inputs\\';

    protected static $inputMap = [
        'twitter' => 'TwitterInput',
        'facebook' => 'FacebookInput',
        'instagram' => 'InstagramInput'
    ];

    /** @var array */
    protected $inputs = [];

    /** @var array */
    protected $inputOptions;

    /** @var StorageInterface */
    protected $storage;

    public function __construct(array $inputOptions, StorageInterface $storage)
    {
        foreach($inputOptions as $name => $options) {
            if (!isset(self::$inputMap[$name])) {
                throw new InputNotFoundException($name);
            }
        }
        $this->inputOptions = $inputOptions;
        $this->storage = $storage;
    }

    /**
     * @param string $name
     * 
     * @return InputInterface
     */
    public function getInput($name)
    {
        if (!isset($this->inputOptions[$name])) {
            throw new InputNotDefinedException($name);
        }

        $className = self::$inputMap[$name];
        if (!isset($this->inputs[$name])) {
            $this->inputs[$name] = $this->buildInput($className, $this->inputOptions[$name]['credentials']);
        }
        
        return $this->inputs[$name];
    }

    /**
     * @return array
     */
    public function getInputs()
    {
        return array_keys($this->inputOptions);
    }

    /**
     * @param string $name
     * 
     * @return array
     */
    public function getInputMap($name)
    {
        return $this->storage->find($name);
    }

    /**
     * @param string $input
     * @param array $map
     */
    public function updateInputMap($input, array $map)
    {
        $this->storage->save($map, $input);
    }
    
    /**
     * @param string $className
     * @param array $credentials
     *
     * @return InputInterface
     */
    protected function buildInput($className, array $credentials)
    {
        $fullClass = self::INPUT_NAMESPACE.$className;

        return new $fullClass($credentials);
    }
}