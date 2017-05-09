<?php

namespace DvsaEntities\DqlBuilder\SearchParam;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommonApi\Model\SearchParam;

/**
 * class OrgSlotUsageParam.
 */
class OrgSlotUsageParam extends SearchParam
{
    protected $organisationId;
    protected $dateFrom = null;
    protected $dateTo = null;
    protected $searchText;

    const SORT_COL_SITE_NUMBER = 'siteNumber';
    const SORT_COL_NAME = 'name';
    const SORT_COL_USAGE = 'usage';

    const DEFAULT_SORT_COL = self::SORT_COL_SITE_NUMBER;

    protected $sortWhiteList = [
        self::SORT_COL_SITE_NUMBER,
        self::SORT_COL_NAME,
        self::SORT_COL_USAGE,
    ];

    /**
     * @param mixed $organisationId
     *
     * @return $this
     */
    public function setOrganisationId($organisationId)
    {
        $this->organisationId = $organisationId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrganisationId()
    {
        return $this->organisationId;
    }

    /**
     * @param string $dateFrom
     *
     * @return $this
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param string $dateTo
     *
     * @return $this
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @param mixed $searchText
     *
     * @return $this
     */
    public function setSearchText($searchText)
    {
        $this->searchText = $searchText;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSearchText()
    {
        return $this->searchText;
    }

    /**
     * @param string $sortColumnID
     *
     * @return SearchParam
     */
    public function setSortColumnId($sortColumnID)
    {
        $this->sortColumnId = $sortColumnID;

        return $this;
    }

    public function loadStandardDataTableValuesFromRequest($request)
    {
        parent::loadStandardDataTableValuesFromRequest($request);
        $this->setSortColumnId($request->getQuery(SearchParamConst::SORT_COLUMN_ID));

        return $this;
    }

    public function getSortName()
    {
        if (array_search($this->getSortColumnId(), $this->sortWhiteList) !== false) {
            return $this->getSortColumnId();
        }

        return self::DEFAULT_SORT_COL;
    }
}
