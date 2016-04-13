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
use Symfony\Component\Yaml\Yaml;

/**
 * Description of Configuration
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class Configuration
{
    public static function setup(Application $app, $configFile)
    {
        $configData = file_get_contents($configFile);
        $config = Yaml::parse($configData);

        foreach($config as $key => $values) {
            if (!isset($app[$key])) {
                $app[$key] = $values;
            }
        }
    }
}