<?php
namespace Handler;

use Pimple\Container;
use Pipeline\DocumentPipeline;
use Pipeline\Payload\DocumentPayload;
use Pipeline\Stage\Document\ParseFrontMatter;
use Pipeline\Stage\Document\RenderTemplate;
use Pipeline\Stage\Document\ResolveDocument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HtmlDocumentHandler
 * @package Handler
 */
class HtmlDocumentHandler
{
    /**
     * @param \Pimple\Container                         $container
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Container $container, Request $request)
    {
        $pipeline = new DocumentPipeline([
            new ResolveDocument($request->getPathInfo(), $container['storage']),
            new ParseFrontMatter($container['parser']),
            new RenderTemplate($container['templates']),
        ]);

        $document = $pipeline->process(new DocumentPayload);

        return new Response($document->getOutput(), 200);
    }
}