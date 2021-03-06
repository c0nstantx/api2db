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
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of InputService
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class InputService
{
    const INPUT_NAMESPACE = '\\Input\\';

    const TIMEOUT_LIMIT = 10;
    
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
     * @param string $driver
     * @param array $map
     *
     * @return bool
     */
    public function endpointIsValid($driver, array $map)
    {
        if (!isset($map['url']) || $map['url'] === '') {
            return false;
        }
        if (!isset($map['owner']) || $map['owner'] === '') {
            return false;
        }
        if (!isset($map['object']) || $map['object'] === '') {
            return false;
        }
        if (!isset($map['id']) || $map['id'] === '') {
            return false;
        }

        return true;
    }

    /**
     * @param string $driver
     * @param int $index
     */
    public function deleteEndpoint($driver, $index)
    {
        $map = $this->getInputMap($driver);

        unset($map[(int)$index]);
        $this->updateInputMap($driver, $map);
    }
    
    /**
     * @param string $driver
     * @param array $map
     */
    public function insertEndpoint($driver, array $map)
    {
        $mapping = [
            'url' => $map['url'],
            'owner' => $map['owner'],
            'object' => $map['object'],
            'id' => $map['id'],
            'map' => $this->buildMapping($map)
        ];

        $map = $this->getInputMap($driver);
        $map[] = $mapping;

        $this->updateInputMap($driver, $map);
    }

    /**
     * @return array
     */
    public function getAvailableInputs()
    {
        return array_keys(self::$inputMap);
    }

    /**
     * @param array $names
     * 
     * @return array
     */
    public function getInputs(array $names)
    {
        $inputs = [];
        
        foreach($names as $name) {
            $inputs[] = $this->getInput($name);
        }
        
        return $inputs;
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
     * @param Request $request
     *
     * @return array
     */
    public function validate(Request $request)
    {
        $required = [
            'input_id',
            'input_name',
            'oauth_type',
        ];
        $oauth1 = [
            'input_identifier',
            'input_secret',
            'input_token1',
            'input_token_secret'
        ];
        $oauth2 = [
            'input_token'
        ];

        $errors = [];

        if ($request->get('oauth_type') === '1') {
            $required = array_merge($required, $oauth1);
        } else if ($request->get('oauth_type') === '2') {
            $required = array_merge($required, $oauth2);
        }

        foreach($required as $req) {
            if (!$request->get($req) || $request->get($req) === '') {
                $errors[] = $req;
            }
        }

        return $errors;
    }

    /**
     * @param Request $request
     * 
     * @return array
     */
    public function getDataFromRequest(Request $request)
    {
        if ($request->get('oauth_type') === '1') {
            $credentials = [
                'identifier' => $request->get('input_identifier'),
                'secret' => $request->get('input_secret'),
                'client_key' => $request->get('input_token1'),
                'client_secret' => $request->get('input_token_secret')
            ];
        } else {
            $credentials = [
                'access_token' => $request->get('input_token')
            ];
        }

        return [
            'name' => $request->get('input_name'),
            'oauth_type' => $request->get('oauth_type'),
            'credentials' => $credentials
        ];
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

    /**
     * @param array $map
     *
     * @return array
     */
    protected function buildMapping(array $map)
    {
        /* TODO: Need a better approach */
        $mapping = [];
        if (isset($map['source']) && count($map['source'])) {
            foreach($map['source'] as $key => $source) {
                if ($source !== '' && $map['relation'][$key] !== '' && $map['dest'][$key] !== '') {
                    $mapping[] = [
                        'source' => $source,
                        'relation' => $map['relation'][$key],
                        'destination' => $map['dest'][$key]
                    ];
                }
            }
        }

        return $mapping;
    }
}