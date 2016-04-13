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
    /** @var array */
    protected $map;

    public function __construct($rawData, array $map = null)
    {
        parent::__construct($rawData);
        $this->map = $map;
    }

    protected function transformData()
    {
        foreach($this->rawData as $rawData) {
            $this->transformedData[] = [
                'subject' => 'kchristofilos',
                'predicate' => 'HAS_TWEET',
                'object' => 'tweet_'.$rawData['id']
            ];
            $this->transformedData[] = [
                'subject' => 'tweet_'.$rawData['id'],
                'predicate' => 'HAS_ID',
                'object' => $rawData['id']
            ];
            $date = new \DateTime($rawData['created_at']);
            $this->transformedData[] = [
                'subject' => 'tweet_'.$rawData['id'],
                'predicate' => 'CREATED_AT',
                'object' => 'time_'.$date->getTimestamp()
            ];
        }
//        $this->map['default'] = [
//            'subject' => 'kchristofilos',
//            'predicate' => 'HAS_TWEET',
//            'object' => 'id'
//        ];
//        $this->map['created_at'] = [
//            'predicate' => 'CREATED_AT',
//        ];
//        $this->map['id'] = [
//            'predicate' => 'HAS',
//        ];
//        $this->map['source'] = [
//            'predicate' => 'HAS_SOURCE',
//        ];
//
//        var_dump($this->map);
//        exit;
//        foreach($this->rawData as $rawData) {
//            $this->transformedData[] = [
//                'subject' => $this->map['default']['subject'],
//                'predicate' => $this->map['default']['predicate'],
//                'object' => $rawData[$this->map['default']['object']]
//            ];
//            var_dump($this->transformedData);
//            exit;
//            var_dump($rawData);
//            exit;
//        }
//        exit;
//        $this->append($this->rawData);
    }
}