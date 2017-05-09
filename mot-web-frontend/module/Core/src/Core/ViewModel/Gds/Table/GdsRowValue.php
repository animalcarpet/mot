<?php

namespace Core\ViewModel\Gds\Table;

class GdsRowValue extends AbstractGdsRowElement
{
    private $content;
    private $escapeRequired;

    public function __construct(GdsRow $parentRow, $content, $escapeRequired = true)
    {
        parent::__construct($parentRow);
        $this->content = $content;
        $this->escapeRequired = $escapeRequired;
    }

    public function isEscapeRequired()
    {
        return $this->escapeRequired;
    }

    public function getContent()
    {
        return $this->content;
    }
}
