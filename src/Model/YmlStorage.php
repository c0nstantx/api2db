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
use Interfaces\StorageInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Description of YmlStorage
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class YmlStorage implements StorageInterface
{
    protected $storageFile;

    public function __construct($storageFile)
    {
        $this->storageFile = $storageFile;
    }

    /**
     * @param mixed $data
     * @param string|null $domain
     */
    public function save($data, $domain = null)
    {
        $fileData = $this->getFileData();

        if (null !== $domain) {
            $fileData[$domain] = $data;
        } else {
            $fileData['default'] = $data;
        }

        $this->saveRaw($fileData);
    }

    /**
     * @param string $domain
     * 
     * @return mixed
     */
    public function find($domain = 'default')
    {
        $fileData = $this->getFileData();

        return isset($fileData[$domain]) ? $fileData[$domain] : [];
    }

    /**
     * @return string
     */
    public function getStorageFile()
    {
        return $this->storageFile;
    }
    
    /**
     * @return array
     */
    public function getFileData()
    {
        $data = file_get_contents($this->storageFile);
        
        return Yaml::parse($data);
    }
    
    public function saveRaw($data)
    {
        file_put_contents($this->storageFile, Yaml::dump($data, 4), LOCK_EX);
    }
}