<?php

namespace DvsaEntities\DqlBuilder\SearchParam;

use DvsaCommonApi\Model\SearchParam;

/**
 * Class SearchParam.
 */
class TesterSearchParam extends SearchParam
{
    protected $search = null;

    public static $columnNames = [
        '0' => 'id',
        '1' => 'username',
    ];

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'search' => $this->getSearch(),
            'sortColumnId' => $this->getSortColumnId(),
            'sortColumnName' => $this->getSortColumnName(),
            'sortDirection' => $this->getSortDirection(),
            'rowCount' => $this->getRowCount(),
            'start' => $this->getStart(),
        ];
    }

    /**
     * @param null $search
     *
     * @return TesterSearchParam
     */
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @return string
     */
    public function getSortColumnName()
    {
        if (isset(self::$columnNames[$this->getSortColumnId()])) {
            return self::$columnNames[$this->getSortColumnId()];
        }

        return null;
    }
}
