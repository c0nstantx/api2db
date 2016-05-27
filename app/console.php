<?php

require __DIR__.'/../vendor/autoload.php';

$debug = true;
$app = require __DIR__.'/../src/app.php';

$consoleApp = new \Symfony\Component\Console\Application();
$consoleApp->add(new \Command\ImportNamesCommand($app['importer_service'], $app['monolog']));
$consoleApp->add(new \Command\ClearEndpointsCommand($app['importer_service'], $app['monolog']));
$consoleApp->add(new \Command\BatchImportCommand(
    $app['input_service'],
    $app['output_service'],
    $app['monolog'],
    $app['inputs'],
    $app['outputs'],
    $app['debug']
));
$consoleApp->add(new \Command\ImportCommand(
    $app['input_service'],
    $app['output_service'],
    $app['ner_service'],
    $app['monolog'],
    $app['outputs'],
    $app['debug']
));
$consoleApp->run();
