<?php

use Dotenv\Dotenv;
use FastRoute\Dispatcher;
use Pimple\Container;
use Pipeline\Factory;
use Pipeline\Payload\HttpRequestPayload;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Kernel
 */
class Kernel
{
    /**
     * @var \Pimple\Container
     */
    protected $container;

    public function __construct()
    {
        $this->container = new Container($this->configureDefaults());

        $this->boot();
    }

    /**
     * @param \FastRoute\Dispatcher $dispatcher
     *
     * @return mixed
     */
    public function run(Dispatcher $dispatcher)
    {
        $request         = Request::createFromGlobals();
        $responseBuilder = Factory::createResponseBuilder($this->container, $dispatcher);

        return $responseBuilder->process(new HttpRequestPayload($request))->getResponse()->send();
    }

    protected function boot()
    {
        (new Dotenv(__DIR__ . '/../'))->load();

        if (getenv('APP_DEBUG')) {
            Debug::enable();
        }
    }

    /**
     * @return array
     */
    protected function configureDefaults()
    {
        return [
            'contentDir'         => __DIR__ . '/../content',
            'parser'             => function () {
                return new \Mni\FrontYAML\Parser();
            },
            'storage'            => function ($c) {
                $local   = new \League\Flysystem\Adapter\Local($c['contentDir']);
                $cache   = new \League\Flysystem\Cached\Storage\Memory();
                $adapter = new \League\Flysystem\Cached\CachedAdapter($local, $cache);

                return new \League\Flysystem\Filesystem($adapter);
            },
            'templatesDir'       => __DIR__ . '/../templates',
            'templatesExtension' => 'phtml',
            'templates'          => function ($c) {
                $templates = new \League\Plates\Engine($c['templatesDir'], $c['templatesExtension']);

                $templates->addFolder('shared', $c['templatesDir'] . '/shared');
                $templates->loadExtension(new \Template\Extension\FinderExtension($c));

                return $templates;
            },
        ];
    }
}