<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */
namespace Model;
use Silex\Application;

/**
 * Description of Router
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class Router
{
    protected $app;
    
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    public function setupRoutes()
    {
        $this->app->get('/', 'Controller\\DefaultController::mainAction')
            ->bind('homepage');

        /* Inputs */
        $this->app->get('/inputs', 'Controller\\InputController::inputsAction')
            ->bind('inputs');
        
        $this->app->get('/input/{driver}', 'Controller\\InputController::viewInputAction')
            ->bind('input_view_existing');
        
        $this->app->get('/input', 'Controller\\InputController::viewInputAction')
            ->bind('input_view');

        $this->app->post('/input', 'Controller\\InputController::createInputAction')
            ->bind('input_create');

        $this->app->post('/input/{driver}', 'Controller\\InputController::updateInputAction')
            ->bind('input_update');
        
        $this->app->delete('/input/{driver}', 'Controller\\InputController::deleteInputAction')
            ->bind('input_delete');

        /* Outputs */
        $this->app->get('/outputs', 'Controller\\DefaultController::outputsAction')
            ->bind('outputs');

        $this->app->get('/output/{driver}', 'Controller\\DefaultController::viewOutputAction')
            ->bind('output_view_existing');
        
        $this->app->get('/output', 'Controller\\DefaultController::viewOutputAction')
            ->bind('output_view');

        $this->app->post('/output', 'Controller\\DefaultController::createOutputAction')
            ->bind('output_create');
        
        $this->app->post('/output/{driver}', 'Controller\\DefaultController::updateOutputAction')
            ->bind('output_update');
        
        $this->app->delete('/output/{driver}', 'Controller\\DefaultController::deleteOutputAction')
            ->bind('output_delete');

        /* Relations */
        $this->app->get('/relations', 'Controller\\RelationController::relationsAction')
            ->bind('relations');
        
        $this->app->get('/relation', 'Controller\\RelationController::viewRelationAction')
            ->bind('relation_view');
        
        $this->app->post('/relation', 'Controller\\RelationController::createRelationAction')
            ->bind('relation_create');
        
        $this->app->delete('/relation/{relation}', 'Controller\\RelationController::deleteRelationAction')
            ->bind('relation_delete');

        /* Endpoints */
        $this->app->get('/{driver}/endpoints', 'Controller\\InputController::endpointsMapAction')
            ->bind('endpoints');

        $this->app->get('/{driver}/endpoint', 'Controller\\InputController::viewEndpointAction')
            ->bind('endpoint_view');

        $this->app->post('/{driver}/endpoint', 'Controller\\InputController::createEndpointAction')
            ->bind('endpoint_create');

        $this->app->delete('/{driver}/endpoint', 'Controller\\InputController::deleteEndpointAction')
            ->bind('endpoint_delete');

        $this->app->post('/{driver}/fetch_input', 'Controller\\InputController::fetchInputAction')
            ->bind('fetch_input');

        /* Run */
        $this->app->get('/run', 'Controller\\DefaultController::runAction')
            ->bind('execute');
    }
}