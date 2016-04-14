<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace Composer;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

/**
 * Description of ScriptHandler
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
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
                $config[$key] = null;
            }

            file_put_contents($configFile, Yaml::dump($config));
        }
    }
}