<?php

use Avolle\WeeklyMatches\CollectMatches;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require __DIR__ . DIRECTORY_SEPARATOR . 'paths.php';
require (ROOT . 'vendor/autoload.php');
require (ROOT . 'functions.php');
$config = require(ROOT . 'config.php');

$log = new Logger('error');
$log->pushHandler(new StreamHandler(LOGS . 'error.log', Logger::ERROR));

$whoops = new Run();
if ($config['debug']) {
    $handler = new PrettyPageHandler();
} else {
    $handler = function() use ($whoops) {
        require TEMPLATES . 'error.php';
    };
}
$whoops->pushHandler($handler);
$whoops->pushHandler(function (Exception $exception) use ($log) {
    $log->error($exception->getMessage() . "\n" . $exception->getTraceAsString());
});
$whoops->register();

if (!isset($_GET['dateFrom']) || !isset($_GET['dateTo'])) {
    require TEMPLATES . 'form.php';
} else {
    $dateFrom = $_GET['dateFrom'];
    $dateTo = $_GET['dateTo'];

    $matches = (new CollectMatches($config['url'], $config['clubId'], $dateFrom, $dateTo))->getMatches();

    $renderClass = $config['renderClass'];
    (new $renderClass($matches))->output();
}
