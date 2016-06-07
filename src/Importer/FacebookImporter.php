<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */

namespace Importer;
use Interfaces\ImporterInterface;
use Model\InputService;

/**
 * Description of FacebookImporter
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class FacebookImporter implements ImporterInterface
{
    /** @var InputService */
    protected $inputService;

    /** @var \Interfaces\InputInterface */
    protected $input;

    public function __construct(InputService $inputService)
    {
        $this->inputService = $inputService;
        $this->input = $inputService->getInput('facebook');
    }

    public function clear()
    {
        $map = $this->inputService->getInputMap('facebook');
        unset($map['imported']);
        $this->inputService->updateInputMap('facebook', $map);
    }

    /**
     * @param array $names
     * @param bool $update
     */
    public function import(array $names, $update = false)
    {
        foreach($names as $name) {
            $results = $this->search($name);
            $this->saveResults($results, $update);
        }
    }

    /**
     * @param string $name
     * @return array
     */
    protected function search($name)
    {
        $results = $this->input->get('https://graph.facebook.com/v2.6/search', [
            'q' => $name,
            'type' => 'page'
        ], []);

        if (is_array($results)) {
            if (isset($results['error'])) {
                throw new \RuntimeException($results['error']['message']);
            }
            return $results;
        }
        
        return [];
    }

    /**
     * @param array $results
     * @param bool $update
     */
    protected function saveResults(array $results, $update = false)
    {
        $map = $this->inputService->getInputMap('facebook');
        foreach($results as $result) {
            if ($update || !isset($map['imported'][$result['id']])) {
                $map['imported'][$result['id']] = $this->buildObject($result['name'], $result['id']);
            }
        }

        $this->inputService->updateInputMap('facebook', $map);
    }

    /**
     * @param string $name
     * @param string $id
     *
     * @return array
     */
    protected function buildObject($name, $id)
    {
        return [
            'url' => $this->buildUrl($id),
            'owner' => $name,
            'object' => $this->input->getType(),
            'id' => 'id',
            'map' => [
                'message'
            ]
        ];
    }

    /**
     * @param string $id
     *
     * @return string
     */
    protected function buildUrl($id)
    {
        return "https://graph.facebook.com/v2.6/$id/posts";
    }
}