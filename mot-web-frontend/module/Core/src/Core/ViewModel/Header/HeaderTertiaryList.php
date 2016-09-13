<?php

namespace Core\ViewModel\Header;

class HeaderTertiaryList
{
    protected $rows = [];

    /**
     * @param string $rowContent
     */
    public function addRow($rowContent)
    {
        $this->rows[] = $rowContent;
    }

    /**
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }
}