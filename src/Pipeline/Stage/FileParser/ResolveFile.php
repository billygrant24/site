<?php
namespace Pipeline\Stage\FileParser;

use Exception\NotFoundException;
use League\Flysystem\Filesystem;
use League\Pipeline\StageInterface;

/**
 * Class ResolveFile
 *
 * @package Pipeline\Stage\FileParser
 */
class ResolveFile implements StageInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $storage;

    /**
     * @param string                       $path
     * @param \League\Flysystem\Filesystem $storage
     */
    public function __construct($path, Filesystem $storage)
    {
        $this->path    = $path;
        $this->storage = $storage;
    }

    /**
     * Process the payload.
     *
     * @param \Pipeline\Payload\DocumentPayload $payload
     *
     * @return mixed
     * @throws \Exception\NotFoundException
     */
    public function process($payload)
    {
        $pathWithExtension = $this->path . '.md';

        if ( ! $this->storage->has($pathWithExtension)) {
            throw new NotFoundException();
        }

        $payload->setUri($this->path);
        $payload->setFile($this->storage->read($pathWithExtension));
        $payload->setLastModified($this->storage->getTimestamp($pathWithExtension));

        return $payload;
    }
}