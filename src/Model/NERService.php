<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace Model;

/**
 * Description of NERService
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
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
        if (!is_string($string)) {
            var_dump($string);
            exit;
        }
        $tags = $this->ner->tag(explode(' ', $string));
        $entities = [];
        foreach($tags as $tag) {
            if ($tag[1] !== 'O') {
                $entities[] = $tag;
            }
        }

        return $entities;
    }
}