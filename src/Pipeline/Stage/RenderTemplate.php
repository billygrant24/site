<?php
namespace Pipeline\Stage;

use League\Pipeline\StageInterface;

/**
 * Class RenderTemplate
 * @package Pipeline\Stage
 */
class RenderTemplate implements StageInterface
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
        $templates = $payload->container['templates'];

        $payload->setOutput($templates->render($payload->getMeta()['template'], [
            'meta'    => $payload->getMeta(),
            'content' => $payload->getContent(),
        ]));

        return $payload;
    }
}