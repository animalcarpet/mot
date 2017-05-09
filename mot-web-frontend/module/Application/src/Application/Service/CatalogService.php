<?php

namespace Application\Service;

use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;
use Zend\Cache\Storage\StorageInterface;

/**
 * This service caches all the Type 2 Lookups. See VM-1793 for a definition.
 */
class CatalogService
{
    private static $catalog;

    const STORAGE_KEY = 'catalog';

    /**
     * @var StorageInterface
     */
    private $appCache;

    /**
     * @var \DvsaCommon\HttpRestJson\Client
     */
    private $restClient;

    /**
     * @param StorageInterface $appCache
     */
    public function __construct(StorageInterface $appCache, $restClient)
    {
        $this->appCache = $appCache;
        $this->restClient = $restClient;
        $this->initCatalog();
    }

    /**
     * @return \DvsaCommon\HttpRestJson\Client
     */
    public function getAuthRestClient()
    {
        return $this->restClient;
    }

    private function initCatalog()
    {
        if (null == self::$catalog) {
            if (!$this->appCache->hasItem(self::STORAGE_KEY)) {
                $apiResult = $this->getAuthRestClient()->get(UrlBuilder::of()->dataCatalog()->toString());

                $catalog = ArrayUtils::tryGet($apiResult, 'data');

                //  --  unserialise to Dto  --
                $dtoHydrator = new DtoHydrator();

                foreach (['reasonsForRefusal', 'reasonsForCancel'] as $key) {
                    $items = ArrayUtils::tryGet($catalog, $key);
                    if (!empty($items)) {
                        $catalog[$key] = $dtoHydrator->doHydration($items);
                    }
                }

                $this->appCache->setItem(self::STORAGE_KEY, $catalog);
            }
            self::$catalog = $this->appCache->getItem(self::STORAGE_KEY);
        }

        return $this;
    }

    private function getCatalog()
    {
        return self::$catalog;
    }

    public function getData()
    {
        return $this->getCatalog();
    }

    public function getDecisions($testType = 'ER')
    {
        static $cacheDecisions;

        if (is_null($cacheDecisions)) {
            foreach ($this->getCatalog()['decisions'] as $val) {
                $cacheDecisions['VE'][$val['id']] = $val['decision'];
                $cacheDecisions['NT'][$val['id']] = $val['decision'];
            }
            unset($cacheDecisions['NT']['2']);
        }

        if ($testType == 'NT') {
            return $cacheDecisions['NT'];
        }

        return $cacheDecisions['VE'];
    }

    public function getCategories()
    {
        static $cacheCategories;

        if (!$cacheCategories) {
            foreach ($this->getCatalog()['categories'] as $val) {
                $cacheCategories[$val['id']] = $val['category'];
            }
        }

        return $cacheCategories;
    }

    /**
     * The "point scores" are used during the process of a Vehicle Examiner
     * scoring the differences between a Tester result and one that they have
     * just performed themselves.
     *
     * These values are used in the SELECT menu in the middle column on the
     * page: /enforcement/mot-test/:id/differences-found-between-tests
     *
     * @return array of values intended for a SELECT control
     */
    public function getScores()
    {
        static $scoreCache = null;

        if (is_null($scoreCache)) {
            foreach ($this->getCatalog()['scores'] as $e) {
                $scoreCache [] = [
                    'id' => $e['id'],
                    'value' => $e['score'],
                    'label' => $e['description'],
                ];
            }
        }

        return $scoreCache;
    }

    /**
     * These are the SELECT values and options for the dropdown on the comparison screen.
     *
     * @return array of values for the Indicative Case Outcome section in the comparison screen
     */
    public function getCaseOutcomeActions()
    {
        static $cacheOutcomes;

        if (is_null($cacheOutcomes)) {
            foreach ($this->getCatalog()['outcomes'] as $x) {
                $cacheOutcomes[$x['id']] = $x['outcome'];
            }
        }

        return $cacheOutcomes;
    }

    /**
     * These are the reasons for AE refusal for conducting MOT test.
     *
     * @return array of possible reasons indexed by reason id
     */
    public function getReasonsForRefusal()
    {
        /** @var \DvsaCommon\Dto\Common\ReasonForRefusalDto $rfr */
        static $cacheReasonsForRefusal;

        if (is_null($cacheReasonsForRefusal)) {
            $rfrs = $this->getCatalog()['reasonsForRefusal'];

            $cacheReasonsForRefusal = [];
            if (is_array($rfrs)) {
                foreach ($rfrs as $rfr) {
                    $cacheReasonsForRefusal[$rfr->getId()] = $rfr;
                }
            }
        }

        return $cacheReasonsForRefusal;
    }

    public function getMotTestTypeDescriptions()
    {
        $descriptions = [];
        foreach ($this->getData()['motTestType'] as $testType) {
            $descriptions[$testType['code']] = $testType['description'];
        }

        return $descriptions;
    }

    public function getColours()
    {
        $colours = [];
        foreach ($this->getData()['colours'] as $colour) {
            $colours[$colour['code']] = $colour['name'];
        }

        return $colours;
    }

    public function getColoursWithIds()
    {
        $colours = [];
        foreach ($this->getData()['colours'] as $colour) {
            $colours[$colour['id']] = $colour['name'];
        }

        return $colours;
    }

    public function getCountriesOfRegistration()
    {
        $countries = [];
        foreach ($this->getData()['countryOfRegistration'] as $country) {
            $countries[$country['id']] = $country['name'];
        }

        return $countries;
    }

    public function getCountriesOfRegistrationByCode()
    {
        $countries = [];
        foreach ($this->getData()['countryOfRegistration'] as $country) {
            if (isset($country['code'])) {
                $countries[$country['code']] = $country['name'];
            }
        }

        return $countries;
    }

    public function getCountries()
    {
        $countries = [];
        foreach ($this->getData()['countries'] as $country) {
            if (isset($country['code'])) {
                $countries[$country['code']] = $country['name'];
            }
        }

        return $countries;
    }

    public function getFuelTypes()
    {
        $types = [];
        foreach ($this->getData()['fuelTypes'] as $type) {
            $types[$type['code']] = $type['name'];
        }

        return $types;
    }

    public function getFuelTypesWithId()
    {
        $types = [];
        foreach ($this->getData()['fuelTypes'] as $type) {
            $types[$type['id']] = $type['name'];
        }

        return $types;
    }

    /**
     * @return array
     */
    public function getPersonSystemRoles()
    {
        return $this->getData()['personSystemRoles'];
    }

    /**
     * Business Roles added to the catalog.
     *
     * @return mixed
     */
    public function getBusinessRoles()
    {
        return $this->getData()['BusinessRoles'];
    }

    public function getSiteBusinessRoles()
    {
        $roles = [];
        foreach ($this->getData()['siteBusinessRole'] as $role) {
            $roles[$role['code']] = $role['name'];
        }

        return $roles;
    }

    public function getBrakeTestTypes()
    {
        $result = [];

        foreach ($this->getData()['brakeTestType'] as $brakeTestType) {
            $result[$brakeTestType['code']] = ucfirst($brakeTestType['name']);
        }

        return $result;
    }

    public function getEquipmentModelStatuses()
    {
        $statuses = [];
        foreach ($this->getData()['equipmentModelStatus'] as $status) {
            $statuses[$status['code']] = $status['name'];
        }

        return $statuses;
    }

    public function getReasonsForEmptyVRM()
    {
        $reasons = [];
        foreach ($this->getData()['reasonsForEmptyVRM'] as $reason) {
            $reasons[$reason['code']] = $reason['name'];
        }

        return $reasons;
    }

    public function getReasonsForEmptyVIN()
    {
        $reasons = [];
        foreach ($this->getData()['reasonsForEmptyVIN'] as $reason) {
            $reasons[$reason['code']] = $reason['name'];
        }

        return $reasons;
    }

    /**
     * A.K.A. auth_for_testing_mot_status.
     *
     * @return array
     */
    public function getQualificationStatus()
    {
        $reasons = [];
        foreach ($this->getData()['qualificationStatus'] as $reason) {
            $reasons[$reason['code']] = $reason['name'];
        }

        return $reasons;
    }

    public function getSiteStatus()
    {
        $types = [];
        foreach ($this->getData()['siteStatus'] as $type) {
            $types[$type['code']] = $type['name'];
        }

        return $types;
    }

    /**
     * @return array
     */
    public function getSiteTypes()
    {
        $types = [];
        foreach ($this->getData()['siteTypes'] as $type) {
            $types[$type['code']] = $type['name'];
        }

        return $types;
    }

    /**
     * @return array
     */
    public function getOrganisationCompanyTypes()
    {
        $types = [];
        foreach ($this->getData()['organisationCompanyTypes'] as $type) {
            $types[$type['code']] = $type['name'];
        }

        return $types;
    }

    /**
     * @param $categoryName
     */
    public function getEventTypesWithOutcomesForCategory($categoryName)
    {
        $categories = isset($this->getData()['eventTypesWithOutcomes']) ?
            $this->getData()['eventTypesWithOutcomes'] : null;

        if (null === $categories) {
            return;
        }

        return isset($categories[$categoryName]) ? $this->getData()['eventTypesWithOutcomes'][$categoryName] : null;
    }

    /**
     * @return array
     */
    public function getAuthForAuthorisedExaminerStatuses()
    {
        $statuses = [];
        foreach ($this->getData()['authForAuthorisedExaminerStatuses'] as $status) {
            $statuses[$status['code']] = $status['name'];
        }

        return $statuses;
    }
}
