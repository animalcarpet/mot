<?php

namespace Core\Action;

class FlashMessage
{
    private $content;
    private $namespace;

    public function __construct(FlashNamespace $namespace, $content)
    {
        $this->content = $content;
        $this->namespace = $namespace;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }
}
