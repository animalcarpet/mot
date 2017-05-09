<?php

namespace Core\ViewModel\Header;

class HeaderTertiaryList
{
    /** @var HeaderTertiaryListElementInterface[] */
    protected $rows = [];

    /**
     * @param string $text
     *
     * @return HeaderTertiaryListElementFlowInterface
     */
    public function addElement($text)
    {
        $element = new HeaderTertiaryListElement($text);
        $this->rows[] = $element;

        return $element;
    }

    public function getElements()
    {
        return $this->rows;
    }
}
