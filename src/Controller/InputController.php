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
    public function endpointsMapAction($driver, Application $app)
    {
        $map = $app['input_service']->getInputMap($driver);

        var_dump($map);
        exit;
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

}