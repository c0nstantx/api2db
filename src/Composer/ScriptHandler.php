<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */

namespace Composer;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

/**
 * Description of ScriptHandler
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class ScriptHandler
{
    public static function buildConfig(Event $event)
    {
        $configFile = 'app/config/config.yml';
        $distConfigFile = 'app/config/config.yml.dist';

        if (!file_exists($configFile)) {
            if (!file_exists($distConfigFile)) {
                throw new FileNotFoundException(null, 0, null, $distConfigFile);
            }

            $distConfig = Yaml::parse(file_get_contents($distConfigFile));

            $distKeys = array_keys($distConfig);
            $config = [];
            foreach($distKeys as $key) {
                $config[$key] = [];
            }

            file_put_contents($configFile, Yaml::dump($config));
        }
    }
}