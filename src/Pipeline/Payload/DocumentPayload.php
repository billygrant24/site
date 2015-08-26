<?php
namespace Pipeline\Payload;

/**
 * Class DocumentPayload
 * @package Pipeline\Payload
 */
class DocumentPayload
{
    public $container;

    protected $meta;

    protected $path;

    protected $uri;

    protected $document;

    protected $parsedDocument;

    protected $content;

    protected $output;

    public function __construct($uri)
    {
        $this->setUri($uri);
    }

    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return mixed
     */
    public function getMeta($key = null, $default = null)
    {
        if (!$key) {
            return $this->meta;
        }

        if (isset($this->meta[$key])) {
            return $this->meta[$key];
        }

        return $default;
    }

    /**
     * @param mixed $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @param mixed $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @param mixed $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @param mixed $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return mixed
     */
    public function getParsedDocument()
    {
        return $this->parsedDocument;
    }

    /**
     * @param mixed $parsedDocument
     */
    public function setParsedDocument($parsedDocument)
    {
        $this->parsedDocument = $parsedDocument;
    }
}