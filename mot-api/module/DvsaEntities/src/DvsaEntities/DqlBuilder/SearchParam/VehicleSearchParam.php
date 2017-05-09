<?php

namespace DvsaEntities\DqlBuilder\SearchParam;

use DvsaCommonApi\Model\SearchParam;
use DvsaCommonApi\Service\Exception\BadRequestException;

/**
 * Class VehicleSearchParam.
 */
class VehicleSearchParam extends SearchParam
{
    protected $search;
    protected $searchType;
    protected $registration;
    protected $vin;

    /**
     * @param string $search
     * @param string $searchType
     */
    public function __construct($search, $searchType = null)
    {
        $this->search = trim($search);
        $this->searchType = strtolower(trim($searchType));
    }

    /**
     * Performs processing of the passed search string into
     * meaningful parts.
     */
    public function process()
    {
        $this->validateInputs();
        $this->setSearchParams();

        return $this;
    }

    /**
     * Set-up search parameters based on search filter.
     */
    public function setSearchParams()
    {
        switch ($this->getSearchType()) {
            case 'vin':
                $this->setVin($this->getSearch());
                break;

            case 'registration':
                $this->setRegistration($this->getSearch());
                break;

            default:
                throw new BadRequestException(
                    'Invalid search filter passed, search must contain valid filter type vin or registration.',
                    BadRequestException::ERROR_CODE_INVALID_DATA
                );

        }
    }

    /**
     * Validate valid search string passed in.
     */
    public function validateInputs()
    {
        if (strlen($this->getSearch()) == 0) {
            throw new BadRequestException(
                'Invalid search passed, search must contain at least one alpha numeric string.',
                BadRequestException::ERROR_CODE_INVALID_DATA
            );
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'format' => $this->getFormat(),
            'search' => $this->getSearch(),
            'searchType' => $this->getSearchType(),
            'registration' => $this->getRegistration(),
            'vin' => $this->getVin(),
            'sortDirection' => $this->getSortDirection(),
            'rowCount' => $this->getRowCount(),
            'start' => $this->getStart(),
        ];
    }

    /**
     * @return string
     */
    public function getSearch()
    {
        return (string) $this->search;
    }

    /**
     * @return string
     */
    public function getSearchType()
    {
        return $this->searchType;
    }

    /**
     * @return string
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @param string $registration
     *
     * @return MotTestSearchParam
     */
    public function setRegistration($registration)
    {
        $this->registration = (string) $registration;

        return $this;
    }

    /**
     * @return string
     */
    public function getVin()
    {
        return $this->vin;
    }

    /**
     * @param string $vin
     *
     * @return MotTestSearchParam
     */
    public function setVin($vin)
    {
        $this->vin = (string) $vin;

        return $this;
    }
}
