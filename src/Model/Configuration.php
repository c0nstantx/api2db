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
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

/**
 * Description of Configuration
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class Configuration
{
    /** @var Application */
    protected $app;

    /** @var YmlStorage */
    protected $configStorage;

    /** @var array */
    protected $config;

    public function __construct(Application $app, $configFile)
    {
        $this->app = $app;
        $this->configStorage = new YmlStorage($configFile);

        $this->config = $this->configStorage->getFileData();
        $this->setup();
    }

    /**
     * @param string $driver
     *
     * @return Configuration
     */
    public function deleteInput($driver)
    {
        if (isset($this->config['inputs'][$driver])) {
            unset($this->config['inputs'][$driver]);
            $this->saveConfig();
        }

        return $this;
    }

    /**
     * @param string $driver
     * @param array $data
     *
     * @return Configuration
     */
    public function addInput($driver, array $data)
    {
        $this->config['inputs'][$driver] = $data;
        $this->saveConfig();

        return $this;
    }

    /**
     * @param string $driver
     * @param array $data
     *
     * @return Configuration
     */
    public function addOutput($driver, array $data)
    {
        $this->config['outputs'][$driver] = $data;
        $this->saveConfig();

        return $this;
    }

    /**
     * @param string $relation
     *
     * @return Configuration
     */
    public function deleteRelation($relation)
    {
        $key = array_search($relation, $this->config['relations']);
        if ($key !== false) {
            unset($this->config['relations'][$key]);
            $this->saveConfig();
        }

        return $this;
    }

    /**
     * @param string $relation
     *
     * @return Configuration
     */
    public function addRelation($relation)
    {
        if (!in_array($relation, $this->config['relations'])) {
            $this->config['relations'][] = $relation;
            $this->saveConfig();
        }

        return $this;
    }

    /**
     * Setup configuration
     */
    protected function setup()
    {
        foreach($this->config as $key => $values) {
            if (!isset($this->app[$key])) {
                $this->app[$key] = $values;
            }
        }
    }

    /**
     * Save configuration to file
     */
    protected function saveConfig()
    {
        $this->configStorage->saveRaw($this->config);
    }
}