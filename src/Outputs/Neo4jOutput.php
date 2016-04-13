<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */
namespace Outputs;
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

    protected function insertData(array $data)
    {
        if (!is_numeric($data['subject']) && !is_numeric($data['object'])) {
            if (!$this->nodeExists($data['subject'])) {
                $this->createNode($data['subject']);
            }
            if (!$this->nodeExists($data['object'])) {
                $this->createNode($data['object']);
            }

            if (!$this->relationExists($data['subject'], $data['predicate'], $data['object'])) {
                $this->createRelation($data['subject'], $data['predicate'], $data['object']);
            }
        }
    }

    /**
     * @param string $source
     * @param string $relation
     * @param string $target
     *
     * @return bool
     */
    protected function relationExists($source, $relation, $target)
    {
        $query = "MATCH (a:$source)-[r:$relation]->(b:$target) RETURN r";
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
        $query = "MATCH(a:$source),(b:$target) CREATE (a)-[r:$relation]->(b)";
        $this->neoClient->sendCypherQuery($query);
    }

    /**
     * @param string $node
     */
    protected function createNode($node)
    {
        $query = "CREATE (n:$node) RETURN n";
        $this->neoClient->sendCypherQuery($query);
    }

    /**
     * @param string $node
     *
     * @return bool
     */
    protected function nodeExists($node)
    {
        $query = "MATCH (n:$node) RETURN n";
        $this->neoClient->sendCypherQuery($query);

        return !empty($this->neoClient->getRows());
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