<?php

require __DIR__ . '/../vendor/autoload.php';

(new Dotenv\Dotenv(__DIR__ . '/../'))->load();

if (getenv('APP_DEBUG')) {
    Symfony\Component\Debug\Debug::enable();
}

$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/{slug:.*}', new \Handler\DefaultDocumentHandler);
});

(new \Kernel)->run($dispatcher);