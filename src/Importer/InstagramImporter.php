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
 * Description of InstagramImporter
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class InstagramImporter implements ImporterInterface
{
    /** @var InputService */
    protected $inputService;

    /** @var \Interfaces\InputInterface */
    protected $input;

    public function __construct(InputService $inputService)
    {
        $this->inputService = $inputService;
        $this->input = $inputService->getInput('instagram');
    }

    public function clear()
    {
        $map = $this->inputService->getInputMap('instagram');
        unset($map['imported']);
        $this->inputService->updateInputMap('instagram', $map);
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
     *
     * @return array|mixed
     */
    protected function search($name)
    {
        $results = $this->input->get('https://api.instagram.com/v1/users/search', [
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
            $media = $this->getUserMedia($result['id']);
            $this->saveMedia($result['full_name'], $media, $update);
        }
    }

    /**
     * @param string $name
     * @param array|\Generator $media
     * @param bool $update
     */
    protected function saveMedia($name, $media, $update = false)
    {
        $map = $this->inputService->getInputMap('instagram');
        foreach($media as $medium) {
            if ($update || !isset($map['imported'][$medium])) {
                $map['imported'][$medium] = $this->buildMediaObject($name, $medium);
            }
        }
        $this->inputService->updateInputMap('instagram', $map);
    }

    /**
     * @param string $name
     * @param string $mediumId
     *
     * @return array
     */
    protected function buildMediaObject($name, $mediumId)
    {
        return [
            'url' => $this->buildCommentUrl($mediumId),
            'owner' => $name,
            'object' => 'instagram_comment',
            'id' => 'id',
            'map' => [
                'text'
            ]
        ];
    }

    /**
     * @param string $id
     *
     * @return string
     */
    protected function buildCommentUrl($id)
    {
        return "https://api.instagram.com/v1/media/$id/comments";
    }

    /**
     * @param string $id
     *
     * @return array|\Generator
     */
    protected function getUserMedia($id)
    {
        $results = $this->input->get("https://api.instagram.com/v1/users/$id/media/recent", [], []);
        if (is_array($results)) {
            foreach($results as $result) {
                if (isset($result['link'])) {
                    yield $this->getIdFromUrl($result['link']);
                }
            }
        }

        return [];
    }

    /**
     * @param $url
     * @return null
     */
    protected function getIdFromUrl($url)
    {
        $id = $this->input->get('https://api.instagram.com/oembed', ['url' => $url], []);

        if (isset($id['media_id'])) {
            return $id['media_id'];
        }

        return null;
    }
}