<?php

namespace UserAdmin\ViewModel;

use DvsaCommon\Dto\Person\SearchPersonResultDto;
use DvsaCommon\Utility\TypeCheck;
use UserAdmin\Controller\UserSearchController;
use UserAdmin\Presenter\PersonPresenter;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Utility\ArrayUtils;
use Zend\View\Model\ViewModel;

/**
 * View model for displaying search user result list.
 */
class UserSearchViewModel extends ViewModel
{
    /* @var PersonPresenter[] $searchResult */
    private $searchResult;
    /** @var array */
    private $searchCriteria;
    /** @var string */
    private $searchCriteriaUsername;
    /** @var string */
    private $searchCriteriaFirstName;
    /** @var string */
    private $searchCriteriaLastName;
    /** @var string */
    private $searchCriteriaEmail;
    /** @var string */
    private $searchCriteriaTown;
    /** @var string */
    private $searchCriteriaDob;
    /** @var string */
    private $searchCriteriaPostcode;

    /**
     * @param SearchPersonResultDto[] $searchResult
     * @param array                   $criteria
     */
    public function __construct($searchResult, $criteria)
    {
        TypeCheck::assertArray($searchResult);
        TypeCheck::assertArray($criteria);

        $this->searchResult = PersonPresenter::decorateList($searchResult);
        $this->searchCriteria = $criteria;

        $this->searchCriteriaUsername = ArrayUtils::tryGet($criteria, UserSearchController::PARAM_USERNAME, '');
        $this->searchCriteriaFirstName = ArrayUtils::tryGet($criteria, UserSearchController::PARAM_FIRSTNAME, '');
        $this->searchCriteriaLastName = ArrayUtils::tryGet($criteria, UserSearchController::PARAM_LASTNAME, '');
        $this->searchCriteriaEmail = ArrayUtils::tryGet($criteria, UserSearchController::PARAM_EMAIL, '');
        $this->searchCriteriaDob = ArrayUtils::tryGet($criteria, UserSearchController::PARAM_DOB, '');
        $this->searchCriteriaTown = ArrayUtils::tryGet($criteria, UserSearchController::PARAM_TOWN, '');
        $this->searchCriteriaPostcode = ArrayUtils::tryGet($criteria, UserSearchController::PARAM_POSTCODE, '');
    }

    /**
     * @return int
     */
    public function getTotalResultNumber()
    {
        return count($this->searchResult);
    }

    /**
     * @return bool
     */
    public function isAnythingFound()
    {
        return $this->getTotalResultNumber() > 0;
    }

    /**
     * @return \UserAdmin\Presenter\PersonPresenter[]
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }

    /**
     * @return string
     */
    public function displayUsernameSearchCriteria()
    {
        return $this->searchCriteriaUsername;
    }

    /**
     * @return string
     */
    public function displayFirstNameSearchCriteria()
    {
        return $this->searchCriteriaFirstName;
    }

    /**
     * @return string
     */
    public function displayLastNameSearchCriteria()
    {
        return $this->searchCriteriaLastName;
    }

    /**
     * @return string
     */
    public function displayEmailSearchCriteria()
    {
        return $this->searchCriteriaEmail;
    }

    /**
     * @return string
     */
    public function displayTownSearchCriteria()
    {
        return $this->searchCriteriaTown;
    }

    /**
     * @return string
     */
    public function displayPostcodeSearchCriteria()
    {
        return $this->searchCriteriaPostcode;
    }

    /**
     * @return string
     */
    public function displayDobSearchCriteria()
    {
        if ($this->searchCriteriaDob === ''
            || false === DateUtils::isValidDate($this->searchCriteriaDob)) {
            return '';
        }

        return DateTimeDisplayFormat::textDate(
            $this->searchCriteriaDob
        );
    }

    /**
     * @return string
     */
    public function displayDobDaySearchCriteria()
    {
        return ArrayUtils::tryGet($this->searchCriteria, UserSearchController::PARAM_DOB_DAY, '');
    }

    /**
     * @return string
     */
    public function displayDobMonthSearchCriteria()
    {
        return ArrayUtils::tryGet($this->searchCriteria, UserSearchController::PARAM_DOB_MONTH, '');
    }

    /**
     * @return string
     */
    public function displayDobYearSearchCriteria()
    {
        return ArrayUtils::tryGet($this->searchCriteria, UserSearchController::PARAM_DOB_YEAR, '');
    }

    /**
     * @return bool
     */
    public function hasSearchCriteria()
    {
        return count($this->searchCriteria) > 0;
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
                    $this->displayUsernameSearchCriteria(),
                    $this->displayFirstNameSearchCriteria(),
                    $this->displayLastNameSearchCriteria(),
                    $this->displayEmailSearchCriteria(),
                    $this->displayDobSearchCriteria(),
                    $this->displayTownSearchCriteria(),
                    $this->displayPostcodeSearchCriteria(),
                ],
                'strlen'
            )
        );
    }

    /**
     * @return array
     */
    public function getAdditionalSearchCriteria()
    {
        return array_filter(
            [
                ArrayUtils::tryGet($this->searchCriteria, UserSearchController::PARAM_EMAIL, ''),
                ArrayUtils::tryGet($this->searchCriteria, UserSearchController::PARAM_DOB_DAY, ''),
                ArrayUtils::tryGet($this->searchCriteria, UserSearchController::PARAM_DOB_MONTH, ''),
                ArrayUtils::tryGet($this->searchCriteria, UserSearchController::PARAM_DOB_YEAR, ''),
                ArrayUtils::tryGet($this->searchCriteria, UserSearchController::PARAM_TOWN, ''),
                ArrayUtils::tryGet($this->searchCriteria, UserSearchController::PARAM_POSTCODE, ''),
            ],
            'strlen'
        );
    }

    /**
     * @return bool
     */
    public function expandAdditionalSearchCriteria()
    {
        return !empty($this->getAdditionalSearchCriteria());
    }
}
