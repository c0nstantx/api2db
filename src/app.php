<?php

require_once __DIR__.'/../vendor/autoload.php';

$debug = isset($debug) ? (bool) $debug : false;

\Symfony\Component\Debug\ErrorHandler::register();
\Symfony\Component\Debug\ExceptionHandler::register($debug);

$app = new \Silex\Application();
$app['debug'] = $debug;

$app['root_dir'] = __DIR__.'/../';
$app['src_dir'] = $app['root_dir'].'src/';

/* Application configuration */
$config = $app['root_dir'].'app/config/config.yml';
\Model\Configuration::setup($app, $config);

/* Input endpoints map */
$inputMap = $app['root_dir'].'app/config/input_map.yml';
if (!file_exists($inputMap)) {
    file_put_contents($inputMap, '', LOCK_EX);
}

/* Input service */
$storage = new \Model\YmlStorage($inputMap);
$inputService = new \Model\InputService($app['input'], $storage);
$app['input_service'] = $inputService;

/* Output service */
$outputService = new \Model\OutputService($app['output']);
$app['output_service'] = $outputService;

$app->get('/', 'Controller\\DefaultController::mainAction');
$app->get('/{path}', 'Controller\\DefaultController::{path}Action');

return $app;