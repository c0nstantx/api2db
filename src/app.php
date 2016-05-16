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
$configFile = $app['root_dir'].'app/config/config.yml';
$app['configuration'] = new \Model\Configuration($app, $configFile);

/* Twig */
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new \Silex\Provider\TwigServiceProvider(), [
    'twig.path' => $app['root_dir'].'resources/views'
]);
$app->extend('twig', function($twig, $app) {
    $twig->addGlobal('inputs', $app['inputs']);
    $twig->addGlobal('outputs', $app['outputs']);
    $twig->addGlobal('relations', $app['relations']);
    $twig->addGlobal('available_inputs', $app['input_service']->getAvailableInputs());
    $twig->addGlobal('available_outputs', $app['input_service']->getAvailableInputs());
    
    return $twig;
});

/* Input endpoints map */
$inputMap = $app['root_dir'].'app/config/input_map.yml';
if (!file_exists($inputMap)) {
    file_put_contents($inputMap, '', LOCK_EX);
}

/* Input service */
$storage = new \Model\YmlStorage($inputMap);
$inputService = new \Model\InputService($app['inputs'], $storage);
$app['input_service'] = $inputService;

/* Output service */
$outputService = new \Model\OutputService($app['outputs']);
$app['output_service'] = $outputService;

/* NER service */
$app['ner_service'] = new \Model\NERService(
    '/Users/kostasx/Downloads/stanford-ner-2015-12-09/classifiers/english.all.3class.distsim.crf.ser.gz', 
    '/Users/kostasx/Downloads/stanford-ner-2015-12-09/stanford-ner.jar', 
    '/Users/kostasx/Downloads/stanford-ner-2015-12-09/lib'
);

$app['router'] = new \Model\Router($app);
$app['router']->setupRoutes();

$app->error(function(\Exception $ex, $code) use ($app) {
    if (!$ex instanceof Twig_Error_Loader) {
        return $app['twig']->render('error.html.twig', ['exception'=>$ex]);
    }
});

return $app;