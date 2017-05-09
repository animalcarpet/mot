<?php

namespace Core\ViewModel\Gds\Table;

use DvsaCommon\Utility\ArrayUtils;

class GdsRow implements GdsRowFlowInterface
{
    private $htmlId = null;
    private $htmlClass = null;
    /** @var GdsTableActionLink */
    private $actionLink = null;

    /** @var GdsRowLabel */
    private $label = null;

    /** @var GdsRowValue */
    private $value = null;

    /** @var GdsRowValueMetaData */
    private $valueMetaData = null;

    private $parentTable;

    /** @var GdsTableActionLink[] */
    private $actionLinks = [];

    public function __construct(GdsTable $parentTable, $htmlId, $htmlClass)
    {
        $this->parentTable = $parentTable;
        $this->htmlId = $htmlId;
        $this->htmlClass = $htmlClass;
    }

    public function getHtmlId()
    {
        return $this->htmlId;
    }

    public function isHtmlClassSet()
    {
        return $this->htmlClass !== null;
    }

    public function getHtmlClass()
    {
        return $this->htmlClass;
    }

    public function setLabel($content, $escape = true)
    {
        $this->label = new GdsRowLabel($this, $content, $escape);

        return $this->label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function addActionLink($text, $url, $tooltip = ' ', $id = '')
    {
        $actionLink = new GdsTableActionLink($this, $text, $url, $tooltip, $id);
        $this->actionLinks[] = $actionLink;

        return $actionLink;
    }

    public function hasActionLink($index = 0)
    {
        return array_key_exists($index, $this->actionLinks);
    }

    public function hasActionLinks()
    {
        return !empty($this->actionLinks);
    }

    public function getActionLink($index = 0)
    {
        return ArrayUtils::tryGet($this->actionLinks, $index);
    }

    public function getActionLinks()
    {
        return $this->actionLinks;
    }

    public function newRow($htmlId = null)
    {
        return $this->parentTable->newRow($htmlId = null);
    }

    public function getRow($index)
    {
        return $this->parentTable->getRow($index);
    }

    public function getRows()
    {
        return $this->parentTable->getRows();
    }

    public function setValue($content, $escape = true)
    {
        $this->value = new GdsRowValue($this, $content, $escape);

        return $this->value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValueMetaData($content, $escape = true)
    {
        $this->valueMetaData = new GdsRowValueMetaData($this, $content, $escape);

        return $this->valueMetaData;
    }

    public function getValueMetaData()
    {
        return $this->valueMetaData;
    }
}
