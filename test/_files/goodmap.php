<?php
$ds       = DIRECTORY_SEPARATOR;
$basePath = realpath(__DIR__ . "$ds..");
return array(
    'LaminasTest\Loader\StandardAutoloaderTest' => $basePath . $ds . 'StandardAutoloaderTest.php',
    'LaminasTest\Loader\ClassMapAutoloaderTest' => $basePath . $ds . 'ClassMapAutoloaderTest.php',
);
