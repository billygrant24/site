<?php
namespace Template\Extension;

use Exception\NotFoundException;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Pimple\Container;
use Pipeline\Factory;
use Pipeline\Payload\Resource;
use Pipeline\Stage\Document\ParseFrontMatter;
use Pipeline\Stage\Document\ResolveFile;
use Utility\Collection;

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
     * @param string $rootPath
     *
     * @return static
     */
    public function find($rootPath = '')
    {
        $paths = $this->container['storage']->listContents($rootPath, true);

        return (new Collection($paths))->filter(function ($pathInfo) {
            return $pathInfo['type'] === 'file' && $pathInfo['extension'] === 'md';
        })->map(function ($pathInfo) {
            return $this->read(str_replace('.md', '', $pathInfo['path']));
        });
    }

    /**
     * @param string $path
     *
     * @return \Pipeline\Payload\Resource
     */
    public function read($path)
    {
        try {
            $document   = new Resource;
            $fileParser = Factory::createFileParser($path, $this->container['storage'], $this->container['parser']);

            return $fileParser->process($document);
        } catch (NotFoundException $e) {
            return $document;
        }
    }
}