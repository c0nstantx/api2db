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
    public function mainAction(Application $app)
    {
        return $app['twig']->render('index.html.twig');
    }

    public function inputsAction(Application $app)
    {
        return $app['twig']->render('input/list.html.twig');
    }

    public function viewInputAction($driver = null, Application $app)
    {
        return $app['twig']->render('input/view.html.twig', [
            'driver' => $driver,
            'input' => $driver ? $app['inputs'][$driver] : null,
            'errors' => []
        ]);
    }

    public function createInputAction(Application $app, Request $request)
    {
        if ($request->isMethod('post')) {
            $errors = $app['input_service']->validate($request);
            if (count($errors)) {
                return $app['twig']->render('input/view.html.twig', [
                    'driver' => null,
                    'input' => null,
                    'errors' => $errors
                ]);
            }
            
            $inputData = $app['input_service']->getDataFromRequest($request);
            $inputId = strtolower(Inflector::getDefault()->camel($request->get('input_id')));
            $app['configuration']->addInput($inputId, $inputData);
        }

        return new RedirectResponse($app['url_generator']->generate('inputs'));
    }

    public function updateInputAction($driver, Request $request, Application $app)
    {
        $request->query->set('input_id', $driver);
        if ($request->isMethod('post')) {
            $errors = $app['input_service']->validate($request);
            if (count($errors)) {
                return $app['twig']->render('input/view.html.twig', [
                    'driver' => null,
                    'input' => null,
                    'errors' => $errors
                ]);
            }

            $inputData = $app['input_service']->getDataFromRequest($request);
            $inputId = strtolower(Inflector::getDefault()->camel($request->get('input_id')));
            $app['configuration']->addInput($inputId, $inputData);
        }

        return new RedirectResponse($app['url_generator']->generate('inputs'));
    }

    public function deleteInputAction($driver, Application $app)
    {
        $config = $app['configuration'];

        $config->deleteInput($driver);

        return null;
    }

    public function testAction(Application $app)
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