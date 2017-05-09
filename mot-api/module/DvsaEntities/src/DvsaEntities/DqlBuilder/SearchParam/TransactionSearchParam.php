<?php

namespace DvsaEntities\DqlBuilder\SearchParam;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommonApi\Model\SearchParam;

/**
 * Class TransactionSearchParam.
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TransactionSearchParam extends SearchParam
{
    protected $dateFrom = null;
    protected $dateTo = null;
    protected $organisationId = null;
    protected $status = null;

    const SORT_COL_COMPLETED_ON = 'completedOn';
    const SORT_COL_SLOTS = 'slots';
    const SORT_COL_AMOUNT = 'amount';
    const SORT_COL_USER = 'createdBy';

    const DEFAULT_SORT_COL = self::SORT_COL_COMPLETED_ON;

    protected $sortWhiteList = [
        self::SORT_COL_COMPLETED_ON,
        self::SORT_COL_SLOTS,
        self::SORT_COL_AMOUNT,
        self::SORT_COL_USER,
    ];

    /**
     * Performs processing of the passed search string into
     * meaningful parts.
     */
    public function process()
    {
        $this->validateInputs();

        return $this;
    }

    /**
     * Apply the rules to the params.
     *
     * @throws \UnexpectedValueException
     */
    protected function validateInputs()
    {
        $hasOrganisationId = strlen($this->getOrganisationId()) > 0;

        if (!$hasOrganisationId) {
            throw new \UnexpectedValueException(
                'Invalid search. OrganisationId must be included'
            );
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'organisationId' => $this->getOrganisationId(),
            'status' => $this->getStatus(),
            'dateFrom' => $this->getDateFrom(),
            'dateTo' => $this->getDateTo(),
        ];
    }

    /**
     * @param int $organisationId
     *
     * @return TransactionSearchParam
     */
    public function setOrganisationId($organisationId)
    {
        $this->organisationId = $organisationId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrganisationId()
    {
        return $this->organisationId;
    }

    /**
     * @param string $status
     *
     * @return TransactionSearchParam
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $dateFrom
     *
     * @return TransactionSearchParam
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
     * @return TransactionSearchParam
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
        if (in_array($this->getSortColumnId(), $this->sortWhiteList) !== false) {
            return $this->getSortColumnId();
        }

        return self::DEFAULT_SORT_COL;
    }
}
