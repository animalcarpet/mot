<?php

namespace Core\ViewModel\Gds\Table;

class GdsTableActionLink extends AbstractGdsRowElement
{
    private $url;
    private $tooltip;
    private $text;
    private $id;

    public function __construct(GdsRow $parentRow, $text, $url, $tooltip, $id = '')
    {
        parent::__construct($parentRow);

        $this->url = $url;
        $this->tooltip = $tooltip;
        $this->text = $text;
        $this->id = $id;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getTooltip()
    {
        return $this->tooltip;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getHtmlId()
    {
        if ($this->id) {
            return $this->id;
        }

        return parent::getHtmlId();
    }
}
