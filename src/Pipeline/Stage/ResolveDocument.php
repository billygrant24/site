<?php
namespace Pipeline\Stage;

use Exception\NotFoundException;
use League\Pipeline\StageInterface;

/**
 * Class ResolveDocument
 * @package Pipeline\Stage
 */
class ResolveDocument implements StageInterface
{
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
        if ( ! $payload->container['storage']->has($payload->getPath())) {
            throw new NotFoundException();
        }

        $payload->setFile($payload->container['storage']->read($payload->getPath()));
        $payload->setLastModified($payload->container['storage']->getTimestamp($payload->getPath()));

        return $payload;
    }
}