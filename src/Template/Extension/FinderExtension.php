<?php
namespace Template\Extension;

use Exception\NotFoundException;
use League\Pipeline\Pipeline;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Pimple\Container;
use Pipeline\Payload\DocumentPayload;
use Pipeline\Stage\ParseFrontMatter;
use Pipeline\Stage\ResolveDocument;

/**
 * Class FinderExtension
 *
 * @package Template\Extension
 */
class FinderExtension implements ExtensionInterface
{
    /**
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * @param \Pimple\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param \League\Plates\Engine $engine
     */
    public function register(Engine $engine)
    {
        $engine->registerFunction('read', [$this, 'read']);
        $engine->registerFunction('find', [$this, 'find']);
    }

    /**
     * @param string $path
     *
     * @return \Pipeline\Payload\DocumentPayload
     */
    public function read($path)
    {
        try {
            $document = new DocumentPayload();
            $pipeline = (new Pipeline())
                ->pipe(new ResolveDocument($path, $this->container['storage']))
                ->pipe(new ParseFrontMatter($this->container['parser']));

            return $pipeline->process($document);
        } catch (NotFoundException $e) {
            return $document;
        }
    }

    /**
     * @param string $rootPath
     *
     * @return static
     */
    public function find($rootPath = '')
    {
        $paths = $this->container['storage']->listContents($rootPath, true);

        return (new \Collection($paths))->filter(function ($pathInfo) {
            return $pathInfo['type'] === 'file' && $pathInfo['extension'] === 'md';
        })->map(function ($pathInfo) {
            return $this->read(str_replace('.md', '', $pathInfo['path']));
        });
    }
}