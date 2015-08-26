<?php

use FastRoute\Dispatcher;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    }

    /**
     * @param \FastRoute\Dispatcher $dispatcher
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception\MethodNotAllowedException
     * @throws \Exception\NotFoundException
     */
    public function run(Dispatcher $dispatcher)
    {
        $request   = Request::createFromGlobals();
        $routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

        try {
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    throw new Exception\NotFoundException($request);
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    throw new Exception\MethodNotAllowedException();
                    break;
                case Dispatcher::FOUND:
                    $handler  = $routeInfo[1];
                    $response = $handler($this->container, $request);
                    break;
            }
        } catch (Exception\NotFoundException $e) {
            $response = new Response($this->container['templates']->render('404'), 404);
        } catch (Exception\MethodNotAllowedException $e) {
            $response = new Response($this->container['templates']->render('405'), 405);
        }

        return $response->send();
    }

    /**
     * @return array
     */
    protected function configureDefaults()
    {
        return [
            'contentDir'         => getcwd() . '/../content',
            'parser'             => function () {
                return new \Mni\FrontYAML\Parser();
            },
            'storage'            => function ($c) {
                $local   = new \League\Flysystem\Adapter\Local($c['contentDir']);
                $cache   = new \League\Flysystem\Cached\Storage\Memory();
                $adapter = new \League\Flysystem\Cached\CachedAdapter($local, $cache);

                return new \League\Flysystem\Filesystem($adapter);
            },
            'templatesDir'       => getcwd() . '/../templates',
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