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
use Model\OutputService;

/**
 * Description of JenaOutput
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class JenaOutput extends AbstractOutput
{
    /**
     * Send data to output
     *
     * @param mixed $data
     */
    public function send(OutputData $data)
    {
        $query = $this->buildQuery($data);
        $this->execute($query);
    }

    /**
     * @param OutputData $data
     * 
     * @return string
     */
    protected function buildQuery(OutputData $data)
    {
        $transformedData = $data->getTransformedData();

        $query = '';
        if (count($transformedData)) {
            $data = $this->processData($transformedData);
            $query .= "PREFIX subjects_ns: <http://custom/ns/subjects#>\nPREFIX relation_ns: <http://custom/ns/relations#>\nPREFIX object_ns: <http://custom/ns/objects#>\nINSERT DATA { \n";
            foreach ($data as $pData) {
                $object = $pData['object']['type'] === 'object' ? 'object_ns:'.$pData['object']['value'] : "'".$pData['object']['value']."'";
                $query .= "\tsubjects_ns:{$pData['subject']} relation_ns:{$pData['predicate']} $object. \n";
            }
            $query .= '}';
        }

        return $query;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function processData(array $data)
    {
        $pData = [];

        foreach($data as $d) {
            $pData[] = [
                'subject' => $d['owner'],
                'predicate' => $d['relation'],
                'object' => [
                    'type' => 'object',
                    'value' => $d['name'].'_'.$d['id']
                ]
            ];
            $pData[] = [
                'subject' => $d['name'].'_'.$d['id'],
                'predicate' => 'HAS_ID',
                'object' => [
                    'type' => 'literal',
                    'value' => $d['id']
                ]
            ];

            foreach($d['attributes'] as $attribute) {
                $pData[] = [
                    'subject' => $d['name'].'_'.$d['id'],
                    'predicate' => $attribute['relation'],
                    'object' => [
                        'type' => 'literal',
                        'value' => $attribute['value']
                    ]
                ];
            }
        }

        return $pData;
    }

    /**
     * @param string $query
     */
    protected function execute($query)
    {
        $this->client->request('POST', $this->outputEndpoint, [
            'connect_timeout' => OutputService::TIMEOUT_LIMIT,
            'form_params' => [
                'update' => $query
            ]
        ]);
    }
}