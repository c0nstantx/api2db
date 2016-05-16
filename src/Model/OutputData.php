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
 * Description of OutputData
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
abstract class OutputData
{
    /** @var mixed */
    protected $rawData;

    /** @var array */
    protected $transformedData;
    
    public function __construct($rawData)
    {
        $this->rawData = $rawData;
    }

    /**
     * @return mixed
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    public function getTransformedData()
    {
        $this->transformData();
        
        return $this->transformedData;
    }
    
    abstract protected function transformData();
}