<?php

use FastRoute\Dispatcher;
use Pimple\Container;
use Pipeline\Factory;
use Pipeline\Payload\WebRequest;
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

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->boot();
    }

    protected function boot()
    {
        if (getenv('APP_DEBUG')) {
            Debug::enable();
        }
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

        return $responseBuilder->process(new WebRequest($request))->getResponse()->send();
    }
}