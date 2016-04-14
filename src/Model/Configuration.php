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
            file_put_contents($this->configFile, Yaml::dump($this->config, 4));
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
}