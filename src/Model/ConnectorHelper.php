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

/**
 * Description of ConnectorHelper
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class ConnectorHelper
{

    /**
     * Append query to url if any options are defined
     *
     * @param string $url
     * @param array $options
     *
     * @return string
     */
    public static function bindUrlOptions($url, array $options = [])
    {
        $urlParts = parse_url($url);
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $existingOptions);
            foreach($options as $key => $value) {
                $existingOptions[$key] = $value;
            }
            $options = $existingOptions;
            $url = $urlParts['scheme'].'://'.$urlParts['host'].$urlParts['path'];
        }
        $query = http_build_query($options);
        if ($query !== '') {
            $url .= "?$query";
        }

        return $url;
    }
}