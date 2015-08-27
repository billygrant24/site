<?php
namespace Pipeline\Stage;

use League\Pipeline\StageInterface;

/**
 * Class ParseFrontMatter
 * @package Pipeline\Stage
 */
class ParseFrontMatter implements StageInterface
{
    /**
     * Process the payload.
     *
     * @param \Pipeline\Payload\DocumentPayload $payload
     *
     * @return mixed
     */
    public function process($payload)
    {
        $document = $payload->container['parser']->parse($payload->getFile());

        $payload->setMeta($document->getYAML());
        $payload->setContent($document->getContent());

        if ( ! $payload->getMeta('date')) {
            $payload->setMeta('date', date('j M Y', $payload->getLastModified()));
        }

        return $payload;
    }
}