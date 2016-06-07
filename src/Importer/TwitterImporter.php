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
 * Description of TwitterImporter
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class TwitterImporter implements ImporterInterface
{
    /** @var InputService */
    protected $inputService;

    /** @var \Interfaces\InputInterface */
    protected $input;

    public function __construct(InputService $inputService)
    {
        $this->inputService = $inputService;
        $this->input = $inputService->getInput('twitter');
    }

    public function clear()
    {
        $map = $this->inputService->getInputMap('twitter');
        unset($map['imported']);
        $this->inputService->updateInputMap('twitter', $map);
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
        $results = $this->input->get('https://api.twitter.com/1.1/users/search.json', [
            'q' => $name
        ], []);

        if (is_array($results)) {
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
        foreach($results as $result) {
            if (isset($result['screen_name'])) {
                $this->saveHandle($result['name'], $result['screen_name'], $update);
            }
        }
    }

    /**
     * @param string $name
     * @param string $handle
     * @param bool $update
     */
    protected function saveHandle($name, $handle, $update = false)
    {
        $map = $this->inputService->getInputMap('twitter');
        if ($update || !isset($map['imported'][$handle])) {
            $map['imported'][$handle] = $this->buildHandleObject($name, $handle);
        }
        $this->inputService->updateInputMap('twitter', $map);
    }

    /**
     * @param string $name
     * @param string $handle
     *
     * @return array
     */
    protected function buildHandleObject($name, $handle)
    {
        return [
            'url' => $this->buildHandleUrl($handle),
            'owner' => $name,
            'object' => $this->input->getType(),
            'id' => 'id_str',
            'map' => [
                'text'
            ]
        ];
    }

    /**
     * @param string $handle
     *
     * @return string
     */
    protected function buildHandleUrl($handle)
    {
        return "https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=$handle";
    }
}