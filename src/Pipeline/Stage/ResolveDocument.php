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
        $path = $payload->getUri() . '.md';

        if ( ! $payload->container['storage']->has($path)) {
            throw new NotFoundException();
        }

        $payload->setPath($path);
        $payload->setDocument($payload->container['storage']->read($payload->getPath()));
        $payload->setMeta([
            'date' => date('j M Y', $payload->container['storage']->getTimestamp($payload->getPath())),
        ]);

        return $payload;
    }
}