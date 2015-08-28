<?php
namespace Handler;

use Pimple\Container;
use Pipeline\Factory;
use Pipeline\Payload\Resource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HtmlHandler
 *
 * @package Handler
 */
class HtmlHandler
{
    /**
     * @param \Pimple\Container                         $container
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Container $container, Request $request)
    {
        $viewBuilder = Factory::createViewBuilder(
            $request->getPathInfo(),
            $container['storage'],
            $container['parser'],
            $container['templates']
        );

        return new Response($viewBuilder->process(new Resource)->getOutput(), 200);
    }
}