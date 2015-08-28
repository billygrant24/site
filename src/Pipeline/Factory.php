<?php
namespace Pipeline;

use FastRoute\Dispatcher;
use League\Flysystem\Filesystem;
use League\Plates\Engine;
use Mni\FrontYAML\Parser;
use Pimple\Container;
use Pipeline\Stage\FileParser\ParseFrontMatter;
use Pipeline\Stage\FileParser\ResolveFile;
use Pipeline\Stage\ResponseBuilder\DispatchHandler;
use Pipeline\Stage\ResponseBuilder\MatchRoute;
use Pipeline\Stage\ViewBuilder\RenderTemplate;

class Factory
{
    /**
     * @param \Pimple\Container     $container
     * @param \FastRoute\Dispatcher $dispatcher
     *
     * @return \Pipeline\ResponseBuilderPipeline
     */
    public static function createResponseBuilder(Container $container, Dispatcher $dispatcher)
    {
        return new ResponseBuilderPipeline([
            new MatchRoute($dispatcher),
            new DispatchHandler($container),
        ]);
    }

    /**
     * @param string                       $pathInfo
     * @param \League\Flysystem\Filesystem $storage
     * @param \Mni\FrontYAML\Parser        $parser
     * @param \League\Plates\Engine        $templates
     *
     * @return \Pipeline\ViewBuilderPipeline
     */
    public static function createViewBuilder($pathInfo, Filesystem $storage, Parser $parser, Engine $templates)
    {
        return new ViewBuilderPipeline([
            static::createFileParser($pathInfo, $storage, $parser),
            new RenderTemplate($templates),
        ]);
    }

    /**
     * @param string                       $pathInfo
     * @param \League\Flysystem\Filesystem $storage
     * @param \Mni\FrontYAML\Parser        $parser
     *
     * @return \Pipeline\FileParserPipeline
     */
    public static function createFileParser($pathInfo, Filesystem $storage, Parser $parser)
    {
        return new FileParserPipeline([
            new ResolveFile($pathInfo, $storage),
            new ParseFrontMatter($parser),
        ]);
    }
}