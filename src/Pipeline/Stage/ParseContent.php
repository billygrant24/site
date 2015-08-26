<?php
namespace Pipeline\Stage;

use League\Pipeline\StageInterface;

/**
 * Class ParseContent
 * @package Pipeline\Stage
 */
class ParseContent implements StageInterface
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
        $payload->setContent($payload->getParsedDocument()->getContent());

        return $payload;
    }
}