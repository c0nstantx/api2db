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

    /** @var string */
    protected $configFile;

    /** @var array */
    protected $config;

    public function __construct(Application $app, $configFile)
    {
        $this->app = $app;
        $this->configFile = $configFile;

        $configData = file_get_contents($this->configFile);
        $this->config = Yaml::parse($configData);

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
        file_put_contents($this->configFile, Yaml::dump($this->config, 4));

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
        $configData = file_get_contents($this->configFile);
        $config = Yaml::parse($configData);

        foreach($config as $key => $values) {
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
        file_put_contents($this->configFile, Yaml::dump($this->config, 4));
    }
}