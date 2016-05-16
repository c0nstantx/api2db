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
 * Description of GraphOutputData
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class GraphOutputData extends OutputData
{
    protected $url;

    protected $owner;

    protected $object;

    protected $id;

    protected $map;

    protected $ownerToSubjectRelation = 'HAS';

    protected $entities = [];
    
    public function __construct($rawData, array $endpointData)
    {
        parent::__construct($rawData);
        $this->url = $endpointData['url'];
        $this->owner = $endpointData['owner'];
        $this->object = $endpointData['object'];
        $this->id = $endpointData['id'];
        $this->map = $endpointData['map'];
    }

    /**
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @param array $entities
     * 
     * @return GraphOutputData
     */
    public function setEntities(array $entities)
    {
        $this->entities = $entities;
        
        return $this;
    }
    
    protected function transformData()
    {
//        $this->buildOwnerRelation();
        if (is_array($this->rawData)) {
            $this->parseArrayData($this->rawData);
        } else {
            $this->parsePlainData($this->rawData);
        }
    }

    protected function buildOwnerRelation()
    {
        foreach($this->map as $map) {
            if ($map['source'] === 'owner' && $map['destination'] === 'object') {
                $this->ownerToSubjectRelation = $map['relation'];
            }
        }
    }

    /**
     * @param array $data
     */
    protected function parseArrayData(array $data)
    {
        foreach($data as $d) {
            $this->parsePlainData($d);
        }
    }

    /**
     * @param array $data
     */
    protected function parsePlainData(array $data)
    {
        $object = $this->createMainObject($data);

        foreach($data as $key => $value) {
            if (is_scalar($value)) {
                $object['attributes'][] = [
                    'relation' => $key,
                    'value' => addslashes(strip_tags($value))
                ];
            }
        }

        $object['entities'] = $this->entities;
        $this->transformedData[] = $object;
    }

    /**
     * @param mixed $key
     *
     * @return bool
     */
    protected function isInMap($key)
    {
        if ($key === 'object' || $key === 'owner') {
            return true;
        }
        foreach($this->map as $map) {
            if ($map['source'] == $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function createMainObject(array $data)
    {
        return [
            'owner' => $this->owner,
            'id' => $data[$this->id],
            'name' => $this->object,
            'relation' => $this->ownerToSubjectRelation,
            'attributes' => []
        ];
    }
}