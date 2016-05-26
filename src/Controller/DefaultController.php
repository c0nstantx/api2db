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

use Guzzle\Inflection\Inflector;
use Model\GraphOutputData;
use Model\NERTagger3;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of DefaultController
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class DefaultController
{
    /**
     * @param Application $app
     *
     * @return Response
     */
    public function mainAction(Application $app)
    {
        return $app['twig']->render('index.html.twig');
    }

    /**
     * @param Application $app
     * 
     * @return Response
     */
    public function outputsAction(Application $app)
    {
        return $app['twig']->render('outputs/list.html.twig');
    }

    /**
     * @param Application $app
     * 
     * @return Response
     */
    public function runAction(Application $app)
    {
        /* Get all inputs */
        $inputService = $app['input_service'];
        $inputs = $inputService->getInputs(array_keys($app['inputs']));

        /* Get all outputs */
        $outputService = $app['output_service'];
        $outputs = $outputService->getOutputs(array_keys($app['outputs']));

        foreach($inputs as $input) {
            $map = $inputService->getInputMap($input->getName());
            if ($map && isset($map['manual'])) {
                $endpoints = $map['manual'];
                foreach ($endpoints as $endpoint) {
                    $rawData = $input->get($endpoint['url']);
                    $inputData = [
                        'raw' => $rawData,
                        'endpoint' => $endpoint
                    ];

                    foreach($outputs as $output) {
                        $data = $outputService->getDataAdapter($output, $inputData);
                        $entities = $app['ner_service']->getEntities($data);
                        $data->setEntities($entities);
                        $output->send($data);
                    }
                }
            }
        }

        return $app['twig']->render('run.html.twig');
    }
}