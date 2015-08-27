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
        $engine->registerFunction('listContents', [$this, 'listContents']);
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
     * @param string   $path
     * @param int|null $limit
     *
     * @return \Pipeline\Payload\DocumentPayload[]
     */
    public function listContents($path, $limit = null)
    {
        $documents = [];

        foreach ($this->container['storage']->listContents($path, true) as $pathInfo) {
            if ($pathInfo['type'] === 'file' && $pathInfo['extension'] === 'md') {
                $document = new DocumentPayload;

                $pipeline = (new Pipeline())
                    ->pipe(new ResolveDocument(str_replace('.md', '', $pathInfo['path']), $this->container['storage']))
                    ->pipe(new ParseFrontMatter($this->container['parser']));

                $documents[] = $pipeline->process($document);
            }
        }

        if ( ! $limit) {
            return $documents;
        }

        return array_slice($documents, 0, $limit);
    }
}