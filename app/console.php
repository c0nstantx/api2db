<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../src/app.php';

$consoleApp = new \Symfony\Component\Console\Application();
$consoleApp->add(new \Command\ImportNamesCommand($app['importer_service']));
$consoleApp->add(new \Command\BatchImportCommand(
    $app['input_service'],
    $app['output_service'],
    $app['ner_service'],
    $app['monolog'],
    $app['inputs'],
    $app['outputs']
));
$consoleApp->run();
