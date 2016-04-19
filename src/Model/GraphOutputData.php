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

    public function __construct($rawData, array $endpointData)
    {
        parent::__construct($rawData);
        $this->url = $endpointData['url'];
        $this->owner = $endpointData['owner'];
        $this->object = $endpointData['object'];
        $this->id = $endpointData['id'];
        $this->map = $endpointData['map'];
    }

    protected function transformData()
    {
        $this->buildOwnerRelation();
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
        foreach($this->map as $map) {
            $key = $map['destination'];
            if ($key === 'owner') {
                $value = $this->owner;
            } else if ($key === 'object') {
                $value = $this->object;
            } else {
                if (isset($data[$key])) {
                    $value = $data[$key];
                } else {
                    $value = null;
                }
            }

            if ($value) {
                $attribute = [
                    'relation' => $map['relation'],
                    'value' => $value,
                ];
                $object['attributes'][] = $attribute;
            }
        }

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