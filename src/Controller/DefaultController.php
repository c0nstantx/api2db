<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */
namespace Controller;
use Model\GraphOutputData;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of DefaultController
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class DefaultController
{
    public function mainAction(\Silex\Application $app)
    {
        $outputService = $app['output_service'];

        $jena = $outputService->getOutput('jena');
        $neo = $outputService->getOutput('neo4j');

        $inputService = $app['input_service'];

        $twitter = $inputService->getInput('twitter');
        $map = $inputService->getInputMap('twitter');

        foreach($map as $endpoint) {
            $rawData = $twitter->get($endpoint);
            $data = new GraphOutputData($rawData);
            $jena->send($data);
            $neo->send($data);
        }

        return new Response('IT WORKS !');
    }
}