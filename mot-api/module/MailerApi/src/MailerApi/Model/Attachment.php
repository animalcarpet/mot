<?php

namespace MailerApi\Model;

class Attachment
{
    protected $content;
    protected $type;
    protected $filename;

    /**
     * Attachment constructor.
     *
     * @param $content
     * @param $type
     * @param $filename
     */
    public function __construct($content, $type, $filename)
    {
        $this->content = $content;
        $this->type = $type;
        $this->filename = $filename;
    }

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }
}
