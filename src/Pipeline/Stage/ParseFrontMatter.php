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
        $payload->setParsedDocument($payload->container['parser']->parse($payload->getDocument()));

        $payload->setMeta(
            array_merge(
                $payload->getMeta(),
                $payload->getParsedDocument()->getYAML()
            )
        );

        return $payload;
    }
}