<?php
namespace Pipeline\Payload;

/**
 * Class Resource
 *
 * @package Pipeline\Payload
 */
class Resource
{
    public $content;

    public $meta;

    public $uri;

    protected $file;

    protected $output;

    protected $lastModified;

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @param mixed $lastModified
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    }

    /**
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return mixed
     */
    public function getMeta($key = null, $default = null)
    {
        if ( ! $key) {
            return $this->meta;
        }

        if (isset($this->meta[$key])) {
            return $this->meta[$key];
        }

        return $default;
    }

    /**
     * @param string|null $key
     * @param mixed|null  $value
     */
    public function setMeta($key = null, $value = null)
    {
        if ( ! $value) {
            $this->meta = $key;

            return;
        }

        $this->meta[$key] = $value;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param mixed $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
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
}