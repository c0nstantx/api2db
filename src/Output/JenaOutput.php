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
                $object = $pData['object']['type'] === 'object' ? 'object_ns:'.$this->canonicalize($pData['object']['value']) : "'".$pData['object']['value']."'";
                $object = $this->sanitize($object);
                $subject = $this->canonicalize($pData['subject']);
                $subject = $this->sanitize($subject);
                $query .= "\tsubjects_ns:$subject relation_ns:{$pData['predicate']} $object. \n";
            }
            $query .= '}';
        }

        return $query;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function canonicalize($value)
    {
        return strtolower(str_ireplace(' ', '_', $value));
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
            if (isset($d['entities'])) {
                foreach($d['entities'] as $entity) {
                    $pData[] = [
                        'subject' => $d['name'].'_'.$d['id'],
                        'predicate' => 'REFERS_TO_'.$entity[1],
                        'object' => [
                            'type' => 'literal',
                            'value' => $entity[0]
                        ]
                    ];
                }
            }

        }

        return $pData;
    }

    /**
     * @param string $query
     */
    protected function execute($query)
    {
        try {
            $this->client->request('POST', $this->outputEndpoint, [
                'connect_timeout' => OutputService::TIMEOUT_LIMIT,
                'form_params' => [
                    'update' => $query
                ]
            ]);
        } catch (\Exception $ex) {
            throw new \RuntimeException("Error while trying to execute query '$query'", 500, $ex);
        }
    }

    /**
     * @param string $content
     *
     * @return string
     */
    protected function sanitize($content)
    {
        $content = str_replace("\n", "", $content);
        $content = str_replace(".", "", $content);

        return $content;
    }
}