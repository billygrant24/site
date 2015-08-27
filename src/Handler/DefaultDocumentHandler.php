<?php
namespace Handler;

use League\Pipeline\Pipeline;
use Pimple\Container;
use Pipeline\Payload\DocumentPayload;
use Pipeline\Stage\ParseFrontMatter;
use Pipeline\Stage\RenderTemplate;
use Pipeline\Stage\ResolveDocument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultDocumentHandler
 * @package Handler
 */
class DefaultDocumentHandler
{
    /**
     * @param \Pimple\Container                         $container
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Container $container, Request $request)
    {
        $pipeline = (new Pipeline)
            ->pipe(new ResolveDocument($request->getPathInfo(), $container['storage']))
            ->pipe(new ParseFrontMatter($container['parser']))
            ->pipe(new RenderTemplate($container['templates']));

        $document = $pipeline->process(new DocumentPayload);

        return new Response($document->getOutput(), 200);
    }
}