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
 * Description of NERService
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class NERService
{
    protected $ner;
    
    public function __construct($classifier, $jar, $libFolder)
    {
        $this->ner = new NERTagger3($classifier, $jar, $libFolder, ['-mx700m']);
    }

    /**
     * @param GraphOutputData $data
     * 
     * @return array
     */
    public function getEntities(GraphOutputData $data)
    {
        $map = $data->getMap();
        $rawData = $data->getRawData();
        $entities = [];
        foreach($rawData as $data) {
            foreach($data as $key => $value) {
                if (in_array($key, $map)) {
                    $ent = $this->getEntitiesFromString($value);
                    $entities = array_merge($entities, $ent);
                }
            }
        }

        return $entities;
    }

    /**
     * @param string $string
     * 
     * @return array
     */
    protected function getEntitiesFromString($string)
    {
        $tags = $this->ner->tag(explode(' ', $string));
        $entities = [];
        foreach($tags as $tag) {
            if ($tag[1] !== 'O') {
                $tag[0] = addslashes($tag[0]);
                $entities[] = $tag;
            }
        }

        return $entities;
    }
}