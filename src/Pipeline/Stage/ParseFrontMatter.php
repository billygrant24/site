<?php
namespace Pipeline\Stage;

use League\Pipeline\StageInterface;
use Mni\FrontYAML\Parser;

/**
 * Class ParseFrontMatter
 * @package Pipeline\Stage
 */
class ParseFrontMatter implements StageInterface
{
    /**
     * @var \Mni\FrontYAML\Parser
     */
    protected $parser;

    /**
     * @param \Mni\FrontYAML\Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Process the payload.
     *
     * @param \Pipeline\Payload\DocumentPayload $payload
     *
     * @return mixed
     */
    public function process($payload)
    {
        $document = $this->parser->parse($payload->getFile());

        $payload->setMeta($document->getYAML());
        $payload->setContent($document->getContent());

        if ( ! $payload->getMeta('date')) {
            $payload->setMeta('date', date('j M Y', $payload->getLastModified()));
        }

        return $payload;
    }
}