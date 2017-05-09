<?php

namespace Core\ViewModel\Header;

/**
 * Represents an element inside of a list in a tertiary title of the view header's.
 *
 * Class HeaderTertiaryListElement
 */
class HeaderTertiaryListElement implements HeaderTertiaryListElementFlowInterface, HeaderTertiaryListElementInterface
{
    private $text;

    private $isBold = false;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function bold()
    {
        $this->isBold = true;
    }

    public function isBold()
    {
        return $this->isBold;
    }

    public function getText()
    {
        return $this->text;
    }
}
