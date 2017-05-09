<?php

namespace Site\ViewModel;

use DvsaClient\ViewModel\AbstractFormModel;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Search\SiteSearchParamsDto;
use DvsaCommon\UrlBuilder\SiteUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use Report\Table\Table;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

/**
 * Class SiteSearchViewModel.
 */
class SiteSearchViewModel extends AbstractFormModel
{
    const NOT_ENOUGH_CHAR = 'Less than 3 Characters Entered (special characters are removed from the search)';
    const NO_RESULT_FOUND = 'No results found';
    const ONE_FIELD_REQUIRED = 'You need to enter some search criteria';
    const ONLY_VEHICLE_CLASS = 'You cannot search by vehicle test classes alone - try expanding your search criteria';

    /**
     * @var string
     */
    private $siteNumber;
    /**
     * @var string
     */
    private $siteName;
    /**
     * @var string
     */
    private $siteTown;
    /**
     * @var string
     */
    private $sitePostcode;
    /**
     * @var array
     */
    private $siteVehicleClass;
    /**
     * @var int
     */
    private $rowCount;
    /**
     * @var int
     */
    private $pageNumber;
    /**
     * @var string
     */
    private $sortBy;
    /**
     * @var string
     */
    private $sortDirection;

    /**
     * @var Table
     */
    private $table;

    /**
     * @return string
     */
    public function getSiteNumber()
    {
        return $this->siteNumber;
    }

    /**
     * @param string $siteNumber
     *
     * @return $this
     */
    public function setSiteNumber($siteNumber)
    {
        $this->siteNumber = $siteNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @param string $siteName
     *
     * @return $this
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteTown()
    {
        return $this->siteTown;
    }

    /**
     * @param string $siteTown
     *
     * @return $this
     */
    public function setSiteTown($siteTown)
    {
        $this->siteTown = $siteTown;

        return $this;
    }

    /**
     * @return string
     */
    public function getSitePostcode()
    {
        return $this->sitePostcode;
    }

    /**
     * @param string $sitePostcode
     *
     * @return $this
     */
    public function setSitePostcode($sitePostcode)
    {
        $this->sitePostcode = $sitePostcode;

        return $this;
    }

    /**
     * @return array
     */
    public function getSiteVehicleClass()
    {
        return $this->siteVehicleClass;
    }

    /**
     * @param array $siteVehicleClass
     *
     * @return $this
     */
    public function setSiteVehicleClass($siteVehicleClass)
    {
        $this->siteVehicleClass = $siteVehicleClass;

        return $this;
    }

    /**
     * @param int $class
     *
     * @return bool
     */
    public function isSiteVehicleClassChecked($class)
    {
        if (!empty($this->siteVehicleClass)) {
            return in_array($class, $this->siteVehicleClass);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }

    /**
     * @param int $rowCount
     *
     * @return $this
     */
    public function setRowCount($rowCount)
    {
        $this->rowCount = $rowCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * @param int $pageNumber
     *
     * @return $this
     */
    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = $pageNumber;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param string $sortBy
     *
     * @return $this
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortDirection()
    {
        return $this->sortDirection;
    }

    /**
     * @param string $sortDirection
     *
     * @return $this
     */
    public function setSortDirection($sortDirection)
    {
        $this->sortDirection = $sortDirection;

        return $this;
    }

    /**
     * @return array
     */
    public function getSiteVehicleClassParameters()
    {
        return [
            [
                'value' => '1',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 1',
                'checked' => $this->isSiteVehicleClassChecked(1),
            ],
            [
                'value' => '2',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 2',
                'checked' => $this->isSiteVehicleClassChecked(2),
            ],
            [
                'value' => '3',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 3',
                'checked' => $this->isSiteVehicleClassChecked(3),
            ],
            [
                'value' => '4',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 4',
                'checked' => $this->isSiteVehicleClassChecked(4),
            ],
            [
                'value' => '5',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 5',
                'checked' => $this->isSiteVehicleClassChecked(5),
            ],
            [
                'value' => '7',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 7',
                'checked' => $this->isSiteVehicleClassChecked(7),
            ],
        ];
    }

    /**
     * Check if at least one search filed is field.
     *
     * @return bool
     */
    public function isValid()
    {
        if ($this->checkValue($this->getSiteNumber()) === false) {
            $this->addError(SiteSearchParamsDto::SITE_NUMBER, self::NOT_ENOUGH_CHAR);
        }
        if ($this->checkValue($this->getSiteName()) === false) {
            $this->addError(SiteSearchParamsDto::SITE_NAME, self::NOT_ENOUGH_CHAR);
        }
        if ($this->checkValue($this->getSiteTown()) === false) {
            $this->addError(SiteSearchParamsDto::SITE_TOWN, self::NOT_ENOUGH_CHAR);
        }
        if ($this->checkValue($this->getSitePostcode()) === false) {
            $this->addError(SiteSearchParamsDto::SITE_POSTCODE, self::NOT_ENOUGH_CHAR);
        }

        return $this->isOneFieldValid();
    }

    /**
     * Check if at least one field is valid.
     *
     * @return bool
     */
    public function isOneFieldValid()
    {
        return (empty($this->getSiteNumber()) === false && $this->checkValue($this->getSiteNumber()) === true)
        || (empty($this->getSiteName()) === false && $this->checkValue($this->getSiteName()) === true)
        || (empty($this->getSiteTown()) === false && $this->checkValue($this->getSiteTown()) === true)
        || (empty($this->getSitePostcode()) === false && $this->checkValue($this->getSitePostcode()) === true);
    }

    /**
     * Check if the value is empty or more than 2 characters length.
     *
     * @param string $value
     *
     * @return bool
     */
    private function checkValue($value)
    {
        $value = $this->deleteUnwantedChar($value);
        $values = explode('-', $value);

        foreach ($values as $val) {
            if (strlen($val) > 2) {
                return true;
            }
        }

        return empty($value) === true ? true : false;
    }

    /**
     * Delete the special char use by the full text search to avoid unwanted search.
     *
     * @param string $string
     *
     * @return string
     */
    private function deleteUnwantedChar($string)
    {
        $string = str_replace(' ', '-', $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '-', $string);

        $string = preg_replace('/-+/', '-', $string);

        return $string;
    }

    /**
     * Check if at least one search field is filed.
     *
     * @return bool
     */
    public function isFormEmpty(FlashMessenger $flashMessenger)
    {
        if (empty($this->getSiteNumber())
            && empty($this->getSiteName())
            && empty($this->getSiteTown())
            && empty($this->getSitePostcode())
        ) {
            if (empty($this->getSiteVehicleClass())) {
                $flashMessenger->addErrorMessage(self::ONE_FIELD_REQUIRED);
            } else {
                $flashMessenger->addErrorMessage(self::ONLY_VEHICLE_CLASS);
            }

            return true;
        }

        return false;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function populateFromQuery(array $data)
    {
        $this->setSiteNumber(ArrayUtils::tryGet($data, SiteSearchParamsDto::SITE_NUMBER));
        $this->setSiteName(ArrayUtils::tryGet($data, SiteSearchParamsDto::SITE_NAME));
        $this->setSiteTown(ArrayUtils::tryGet($data, SiteSearchParamsDto::SITE_TOWN));
        $this->setSitePostcode(ArrayUtils::tryGet($data, SiteSearchParamsDto::SITE_POSTCODE));
        $this->setSiteVehicleClass(ArrayUtils::tryGet($data, SiteSearchParamsDto::SITE_VEHICLE_CLASS));
        $this->setRowCount(ArrayUtils::tryGet($data, SearchParamConst::ROW_COUNT));
        $this->setPageNumber(ArrayUtils::tryGet($data, SearchParamConst::PAGE_NR));
        $this->setSortBy(ArrayUtils::tryGet($data, SearchParamConst::SORT_BY));
        $this->setSortDirection(ArrayUtils::tryGet($data, SearchParamConst::SORT_DIRECTION));

        return $this;
    }

    /**
     * @return SiteSearchParamsDto
     */
    public function prepareSearchParams()
    {
        return (new SiteSearchParamsDto())
            ->setSiteNumber($this->getSiteNumber())
            ->setSiteName($this->getSiteName())
            ->setSiteTown($this->getSiteTown())
            ->setSitePostcode($this->getSitePostcode())
            ->setSiteVehicleClass($this->getSiteVehicleClass())
            ->setRowsCount($this->getRowCount() === null ? 10 : $this->getRowCount())
            ->setPageNr($this->getPageNumber() === null ? 1 : $this->getPageNumber())
            ->setSortBy($this->getSortBy() === null ? 'siteName' : $this->getSortBy())
            ->setSortDirection($this->getSortDirection() === null ? 'ASC' : $this->getSortDirection());
    }

    /**
     * @param Table $table
     *
     * @return $this
     */
    public function setTable(Table $table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return int
     */
    public function getTotalResults()
    {
        return $this->table->getRowsTotalCount();
    }

    /**
     * @return string
     */
    public function displaySearchCriteria()
    {
        return implode(
            ', ',
            array_filter(
                [
                    $this->getSiteNumber(),
                    $this->getSiteName(),
                    $this->getSiteTown(),
                    $this->getSitePostcode(),
                    empty($this->getSiteVehicleClass()) === false
                    ? implode(', ', $this->getSiteVehicleClass())
                    : '',
                ],
                'strlen'
            )
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            SiteSearchParamsDto::SITE_NUMBER => $this->getSiteNumber(),
            SiteSearchParamsDto::SITE_NAME => $this->getSiteName(),
            SiteSearchParamsDto::SITE_TOWN => $this->getSiteTown(),
            SiteSearchParamsDto::SITE_POSTCODE => $this->getSitePostcode(),
            SiteSearchParamsDto::SITE_VEHICLE_CLASS => $this->getSiteVehicleClass(),
        ];
    }

    /**
     * @return string
     */
    public function getSearchPage()
    {
        return SiteUrlBuilderWeb::search().'?'.http_build_query($this->toArray());
    }

    /**
     * @return string
     */
    public function getResultPage()
    {
        return SiteUrlBuilderWeb::result();
    }
}
