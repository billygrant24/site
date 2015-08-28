<?php
namespace Pipeline\Stage\ResponseBuilder;

use Exception\MethodNotAllowedException;
use Exception\NotFoundException;
use FastRoute\Dispatcher;
use League\Pipeline\StageInterface;

/**
 * Class DispatchHandler
 *
 * @package Pipeline\Stage\ResponseBuilder
 */
class DispatchHandler implements StageInterface
{
    /**
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * DispatchHandler constructor.
     *
     * @param \Pimple\Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Process the payload.
     *
     * @param \Pipeline\Payload\DocumentPayload $payload
     *
     * @return mixed
     */
    public function process($payload)
    {
        $routeInfo = $payload->getRoute();

        try {
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    throw new NotFoundException;
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    throw new MethodNotAllowedException;
                    break;
                case Dispatcher::FOUND:
                    $handler  = $routeInfo[1];
                    $response = $handler($this->container, $payload->getRequest());
                    break;
            }
        } catch (NotFoundException $e) {
            $response = new Response($this->container['templates']->render('404'), 404);
        } catch (MethodNotAllowedException $e) {
            $response = new Response($this->container['templates']->render('405'), 405);
        }

        $payload->setResponse($response);

        return $payload;
    }
}