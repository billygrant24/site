<?php

chdir(dirname(__DIR__));
setlocale(LC_ALL, 'en_GB');

require __DIR__ . '/../vendor/autoload.php';

$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/{slug:.*}', new \Handler\HtmlDocumentHandler);
});

(new Kernel)->run($dispatcher);