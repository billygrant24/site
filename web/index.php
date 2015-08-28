<?php

require __DIR__ . '/../vendor/autoload.php';

(new \Dotenv\Dotenv(__DIR__ . '/../'))->load();

$container = new \Pimple\Container();

$container->register(new \ServiceProvider\ParserServiceProvider());
$container->register(new \ServiceProvider\StorageServiceProvider());
$container->register(new \ServiceProvider\TemplatesServiceProvider());

$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/{slug:.*}', new \Handler\HtmlHandler);
});

(new Kernel($container))->run($dispatcher);