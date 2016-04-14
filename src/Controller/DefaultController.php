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
    public function inputsAction(Application $app)
    {
        return $app['twig']->render('input/list.html.twig');
    }

    /**
     * @param null|string $driver
     * @param Application $app
     *
     * @return Response
     */
    public function viewInputAction($driver = null, Application $app)
    {
        return $app['twig']->render('input/view.html.twig', [
            'driver' => $driver,
            'input' => $driver ? $app['inputs'][$driver] : null,
            'errors' => []
        ]);
    }

    /**
     * @param Application $app
     * @param Request $request
     *
     * @return RedirectResponse
     */
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

    /**
     * @param string $driver
     * @param Request $request
     * @param Application $app
     *
     * @return RedirectResponse
     */
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

    /**
     * @param string $driver
     * @param Application $app
     *
     * @return Response
     */
    public function deleteInputAction($driver, Application $app)
    {
        $config = $app['configuration'];

        $config->deleteInput($driver);

        return new Response();
    }

    /**
     * @param Application $app
     *
     * @return Response
     */
    public function relationsAction(Application $app)
    {
        return $app['twig']->render('relations/list.html.twig');
    }

    /**
     * @param $relation
     * @param Application $app
     *
     * @return Response
     */
    public function deleteRelationAction($relation, Application $app)
    {
        $config = $app['configuration'];

        $config->deleteRelation($relation);

        return new Response();
    }

    /**
     * @param Application $app
     *
     * @return Response
     */
    public function viewRelationAction(Application $app)
    {
        return $app['twig']->render('relations/view.html.twig', ['errors' => []]);
    }

    /**
     * @param Application $app
     *
     * @return Response
     */
    public function createRelationAction(Application $app, Request $request)
    {
        $errors = [];
        $relation = $request->get('relation');
        if (!$relation || $relation === '') {
            $errors[] = 'relation';
            return $app['twig']->render('relations/view.html.twig', ['errors' => $errors]);
        }
        
        $app['configuration']->addRelation($relation);
        return new RedirectResponse($app['url_generator']->generate('relations'));
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
        $inputService = $app['input_service'];
        $inputs = $inputService->getInputs(array_keys($app['inputs']));

        $outputService = $app['output_service'];
        $outputs = $outputService->getOutputs(array_keys($app['outputs']));

        foreach($inputs as $input) {
            $map = $inputService->getInputMap($input->getName());
            foreach ($map as $endpoint) {
                $rawData = $input->get($endpoint);
                $data = new GraphOutputData($rawData);

                foreach($outputs as $output) {
                    $output->send($data);
                }
            }
        }

        return $app['twig']->render('run.html.twig');
    }
}