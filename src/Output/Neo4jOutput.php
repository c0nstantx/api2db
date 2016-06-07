<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */
namespace Output;
use Interfaces\InputInterface;
use Model\GraphOutputData;
use Model\OutputData;
use Neoxygen\NeoClient\Client;
use Neoxygen\NeoClient\ClientBuilder;

/**
 * Description of Neo4jOutput
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class Neo4jOutput extends AbstractOutput
{
    /** @var Client */
    protected $neoClient;

   /**
     * Send data to output
     *
     * @param mixed $data
     */
    public function send(OutputData $data)
    {
        $transformedData = $data->getTransformedData();

        foreach($transformedData as $tData) {
            $this->insertData($tData);
        }
    }

    /**
     * @param array $owner
     * @param array $input
     * 
     * @return GraphOutputData
     */
    public function reconstructData(array $owner, array $input)
    {
        $endpointData = [
            'url' => '',
            'owner' => $owner['name'],
            'object' => $owner['type'],
            'id' => $owner['id'],
            'map' => $owner['map']
        ];
        
        return new GraphOutputData([$input], $endpointData);
    }

    /**
     * @param InputInterface $input
     *
     * @return array|null
     */
    public function fetchData(InputInterface $input)
    {
        $query = "MATCH (owner)-[]->(input:{$input->getType()}) RETURN owner,input";
        $this->neoClient->sendCypherQuery($query);

        return $this->neoClient->getRows();
    }

    /**
     * @param array $data
     */
    protected function insertData(array $data)
    {
        $ownerAttrs = [
            'name' => $data['owner'],
            'type' => $data['name']
        ];
        if (!$this->nodeExists('Owner', $ownerAttrs)) {
            $this->createNode('Owner', $ownerAttrs);
        }
        if (!$this->nodeExists($data['name'], ['id' => $data['id']])) {
            $attributes = [];
            foreach($data['attributes'] as $attr) {
                $attributes[$attr['relation']] = $attr['value'];
            }
            $attributes['id'] = $data['id'];
            $this->createNode($data['name'], $attributes);
        }

        $subject = [
            'type' => 'Owner',
            'attributes' => ['name' => $data['owner']]
        ];
        $object = [
            'type' => $data['name'],
            'attributes' => ['id' => $data['id']]
        ];
        if (!$this->relationExists($subject, $data['relation'], $object)) {
            $this->createRelation($subject, $data['relation'], $object);
        }

        if (isset($data['entities'])) {
            $entities = $data['entities'];
            foreach($entities as $entity) {
                $attributes = [
                    'name' => $entity[0]
                ];
                if (!$this->nodeExists($entity[1], $attributes)) {
                    $this->createNode($entity[1], $attributes);
                }

                $subject = [
                    'type' => $entity[1],
                    'attributes' => ['name' => $entity[0]]
                ];
                $object = [
                    'type' => $data['name'],
                    'attributes' => ['id' => $data['id']]
                ];
                if (!$this->relationExists($object, 'REFERS', $subject)) {
                    $this->createRelation($object, 'REFERS', $subject);
                }
            }
        }
    }

    /**
     * @param array $source
     * @param string $relation
     * @param array $target
     *
     * @return bool
     */
    protected function relationExists(array $source, $relation, array $target)
    {
        $sourceQuery = $this->getQueryStringForAttributes($source['attributes']);
        $targetQuery = $this->getQueryStringForAttributes($target['attributes']);
        $query = "MATCH (a:{$source['type']}$sourceQuery)-[r:$relation]->(b:{$target['type']}$targetQuery) RETURN r";
        $this->neoClient->sendCypherQuery($query);

        return !empty($this->neoClient->getRows());
    }

    /**
     * @param string $source
     * @param string $relation
     * @param string $target
     */
    protected function createRelation($source, $relation, $target)
    {
        $sourceQuery = $this->getQueryStringForAttributes($source['attributes']);
        $targetQuery = $this->getQueryStringForAttributes($target['attributes']);
        $query = "MATCH(a:{$source['type']}$sourceQuery),(b:{$target['type']}$targetQuery) CREATE (a)-[r:$relation]->(b)";
        $this->neoClient->sendCypherQuery($query);
    }

    /**
     * @param string $node
     */
    protected function createNode($node, array $attributes = [])
    {
        $attrQuery = $this->getQueryStringForAttributes($attributes);
        $query = "CREATE (n:$node$attrQuery) RETURN n";

        $this->neoClient->sendCypherQuery($query);
    }

    /**
     * @param string $node
     *
     * @return bool
     */
    protected function nodeExists($node, array $attributes = [])
    {
        $attrQuery = $this->getQueryStringForAttributes($attributes);
        $query = "MATCH (n:$node$attrQuery) RETURN n";
        $this->neoClient->sendCypherQuery($query);

        return !empty($this->neoClient->getRows());
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    protected function getQueryStringForAttributes(array $attributes = [])
    {
        $queryParams = [];
        foreach($attributes as $key => $value) {
            if (is_string($value)) {
                $queryParams[] = " $key : '$value' ";
            }
        }

        $attrQuery = '';
        if (count($queryParams)) {
            $attrQuery .= ' { '.implode(', ', $queryParams).' } ';
        }

        return $attrQuery;
    }

    /**
     * @param array $options
     */
    protected function parseExtraOptions(array $options)
    {
        $client = ClientBuilder::create()
            ->setAutoFormatResponse(true);
        if (isset($options['credentials'])) {
            $client->addConnection('default', $this->config['schema'],
                $this->config['host'], $this->config['port'], true,
                $options['credentials']['username'], $options['credentials']['password']);
        } else {
            $client->addConnection('default', $this->config['schema'],
                $this->config['host'], $this->config['port']);
        }
        $this->neoClient = $client->build();

        parent::parseExtraOptions($options);
    }
}