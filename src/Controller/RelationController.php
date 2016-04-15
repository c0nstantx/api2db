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
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of RelationController
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class RelationController
{
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

}