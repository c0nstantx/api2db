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
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of InputController
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class InputController
{
    /**
     * @param string $driver
     * @param Application $app
     * 
     * @return Response
     */
    public function endpointsMapAction($driver, Application $app)
    {
        $endpoints = $app['input_service']->getInputMap($driver);

        if (isset($endpoints['manual'])) {
            $manualEndpoints = $endpoints['manual'];
        } else {
            $manualEndpoints = [];
        }

        return $app['twig']->render('input/endpoints.html.twig', [
            'driver' => $driver,
            'endpoints' => $manualEndpoints
        ]);
    }

    /**
     * @param string $driver
     * @param string $index
     * @param Application $app
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function deleteEndpointAction($driver, $index, Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $app['input_service']->deleteEndpoint($driver, $index);
            $response = [
                'status' => 'error',
                'message' => 'Unknown error'
            ];
            return new JsonResponse($response);
        }

        return new RedirectResponse($app['url_generator']->generate('homepage'));
    }

    /**
     * @param $driver
     * @param Application $app
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function createEndpointAction($driver, Application $app, Request $request)
    {
        $inputService = $app['input_service'];
        
        $map = $request->request->all();
        if ($inputService->endpointIsValid($driver, $map)) {
            $inputService->insertEndpoint($driver, $map);
        }

        return new RedirectResponse($app['url_generator']->generate('endpoints', ['driver'=>$driver]));
    }

    /**
     * @param $driver
     * @param Application $app
     *
     * @return Response
     */
    public function viewEndpointAction($driver, Application $app)
    {
        return $app['twig']->render('input/view_endpoint.html.twig', [
            'driver' => $driver
        ]);
    }

    /**
     * @param string $driver
     * @param Application $app
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function fetchInputAction($driver, Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $response = [
                'status' => 'error',
                'message' => 'Unknown error'
            ];
            
            if (!$request->get('url')) {
                $response['message'] = 'No url is defined';
            }
            $inputService = $app['input_service'];
            $input = $inputService->getInput($driver);

            try {
                $data = $input->get($request->get('url'));
                if (!$data) {
                    throw new \RuntimeException('No response was returned');
                }
                $response['status'] = 'success';
                $response['message'] = $data;
            } catch (\Exception $ex) {
                $response['message'] = $ex->getMessage();
            }

            return new JsonResponse($response);
        }
        
        return new RedirectResponse($app['url_generator']->generate('homepage'));
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

        return new RedirectResponse($app['url_generator']->generate('homepage'));
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

}