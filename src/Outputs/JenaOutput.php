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
            $query .= "PREFIX subjects_ns: <http://custom/ns/subjects#>\nPREFIX relation_ns: <http://custom/ns/relations#>\nINSERT DATA { \n";
            foreach ($transformedData as $tData) {
                $query .= "\tsubjects_ns:{$tData['subject']} relation_ns:{$tData['predicate']} '{$tData['object']}'. \n";
            }
            $query .= '}';
        }
        return $query;
    }

    /**
     * @param string $query
     */
    protected function execute($query)
    {
        $this->client->request('POST', $this->outputEndpoint, [
            'form_params' => [
                'update' => $query
            ]
        ]);
    }
}