<?php

namespace Core\ViewModel\Gds\Table;

use DvsaCommon\Utility\ArrayUtils;

class GdsTable implements GdsTableFlowInterface
{
    /** @var GdsRow[] */
    private $rows = [];

    private $headerCssClass = '';
    private $headerText = '';

    public function newRow($htmlId = null, $htmlClass = null)
    {
        $row = new GdsRow($this, $htmlId, $htmlClass);
        $this->rows[] = $row;

        return $row;
    }

    public function setHeader($text, $cssClass = null)
    {
        $this->headerText = $text;
        $this->headerCssClass = $cssClass;

        return $this;
    }

    public function getHeaderCssClass()
    {
        return $this->headerCssClass;
    }

    public function getHeaderText()
    {
        return $this->headerText;
    }

    public function isHeaderSet()
    {
        return $this->headerText == '' ? false : true;
    }

    public function getHeaderColspan()
    {
        return 2;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function hasBody()
    {
        return !empty($this->rows);
    }

    public function getRow($index)
    {
        $row = ArrayUtils::tryGet($this->rows, $index, null);

        if ($row === null) {
            throw new \OutOfBoundsException('Index '.$index.' does not exist.');
        }

        return $this->rows[$index];
    }
}
