<?php

namespace DvsaEntities\DqlBuilder\SearchParam;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommonApi\Model\SearchParam;

/**
 * Class SiteSlotUsageParam.
 */
class SiteSlotUsageParam extends SearchParam
{
    const SORT_COL_DATE = 'date';
    const SORT_COL_TESTER = 'tester';
    const SORT_COL_VRN = 'vrn';

    const DEFAULT_SORT_COL = self::SORT_COL_DATE;

    protected $sortWhiteList = [
        self::SORT_COL_DATE,
        self::SORT_COL_TESTER,
        self::SORT_COL_VRN,
    ];

    protected $dateFrom = null;
    protected $dateTo = null;

    protected $vtsId;

    /**
     * @param string $sortColumnID
     *
     * @return $this
     */
    public function setSortColumnId($sortColumnID)
    {
        $this->sortColumnId = $sortColumnID;

        return $this;
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
     * @param mixed $vtsId
     *
     * @return $this
     */
    public function setVtsId($vtsId)
    {
        $this->vtsId = $vtsId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVtsId()
    {
        return $this->vtsId;
    }

    public function loadStandardDataTableValuesFromRequest($request)
    {
        parent::loadStandardDataTableValuesFromRequest($request);
        $this->setSortColumnId($request->getQuery(SearchParamConst::SORT_COLUMN_ID));

        return $this;
    }

    public function getSortName()
    {
        if (in_array($this->getSortColumnId(), $this->sortWhiteList) !== false) {
            return $this->getSortColumnId();
        }

        return self::DEFAULT_SORT_COL;
    }
}
