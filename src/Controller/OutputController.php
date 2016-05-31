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

/**
 * Description of OutputController
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class OutputController
{
    /**
     * @param string $driver
     * @param Application $app
     * 
     * @return mixed
     */
    public function viewOutputAction($driver, Application $app)
    {
        return $app['twig']->render('output/view.html.twig', [
            'driver' => $driver,
            'output' => $driver ? $app['outputs'][$driver] : null,
            'errors' => []
        ]);
        
    }

    /**
     * @param string $driver
     * @param Request $request
     * @param Application $app
     * 
     * @return RedirectResponse
     */
    public function updateOutputAction($driver, Request $request, Application $app)
    {
        $request->query->set('output_id', $driver);
        if ($request->isMethod('post')) {
            $errors = $app['output_service']->validate($request);
            $outputData = $app['output_service']->getDataFromRequest($request);
            if (count($errors)) {
                return $app['twig']->render('output/view.html.twig', [
                    'driver' => $driver,
                    'output' => $outputData,
                    'errors' => $errors
                ]);
            }

            $app['configuration']->addOutput($driver, $outputData);
        }
        
        return new RedirectResponse($app['url_generator']->generate('homepage'));
    }
}