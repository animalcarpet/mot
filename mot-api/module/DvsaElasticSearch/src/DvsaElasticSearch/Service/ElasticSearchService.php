<?php

namespace DvsaElasticSearch\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaElasticSearch\Query\FbQueryMotTest;
use DvsaElasticSearch\Query\FbQueryMotTestLog;
use DvsaElasticSearch\Query\FbQuerySite;
use DvsaElasticSearch\Query\FbQueryVehicle;
use DvsaElasticSearch\Query\SuperSearchQuery;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\DqlBuilder\SearchParam\VehicleTestingStationSearchParam;
use DvsaEntities\DqlBuilder\SearchParam\VehicleSearchParam;
use DvsaCommonApi\Model\SearchParam;
use DvsaEntities\Repository\SiteRepository;
use DvsaFeature\FeatureToggles;
use Zend\Di\ServiceLocatorInterface;
use Zend\Http\Request;

/**
 * Class ElasticSearchService
 *
 * This class is responsible for handling all Elasatic Search based "super search" queries
 * from the main API.
 *
 * Currently it supports:
 *
 *    - MOT Tests by
 *    - Vehicles by
 *
 * @package DvsaElasticSearch\Service
 */
class ElasticSearchService
{
    /** @var AuthorisationServiceInterface */
    protected $authService;

    /** @var SiteRepository $siteRepository */
    private $siteRepository;

    /** @var FeatureToggles $featureToggles */
    private $featureToggles;

    const NORMAL_TEST_TEST_TYPE = 'Normal Test';
    const MYSTERY_SHOPPER_TEST_TYPE = 'Mystery Shopper';

    /**
     * This creates the ES search service. It requires the following services:
     *
     * @param AuthorisationServiceInterface $authService
     * @param SiteRepository $siteRepository
     * @param FeatureToggles $featureToggles
     */
    public function __construct(
        AuthorisationServiceInterface $authService,
        SiteRepository $siteRepository,
        FeatureToggles $featureToggles
    ) {
        $this->authService = $authService;
        $this->siteRepository = $siteRepository;
        $this->featureToggles = $featureToggles;
    }

    /**
     * Search for MOT tests.
     *
     * @param MotTestSearchParam $params
     *
     * @return array
     * @throws \UnexpectedValueException
     */
    public function findTests(MotTestSearchParam $params)
    {
        $this->checkPermissions(PermissionInSystem::MOT_TEST_LIST);

        $optionalMotTestTypes = [];

        if ($this->featureToggles->isEnabled(FeatureToggle::MYSTERY_SHOPPER) &&
            $this->authService->isGranted(PermissionInSystem::VIEW_MYSTERY_SHOPPER_TESTS)) {
            $optionalMotTestTypes = array_merge($optionalMotTestTypes, [MotTestTypeCode::MYSTERY_SHOPPER]);
        }

        if ($this->featureToggles->isEnabled(FeatureToggle::MYSTERY_SHOPPER) &&
            $this->authService->isGranted(PermissionInSystem::VIEW_NON_MOT_TESTS)) {
            $optionalMotTestTypes = array_merge($optionalMotTestTypes, [MotTestTypeCode::NON_MOT_TEST]);
        }

        /** @var SearchResultDto $result */
        $result = SuperSearchQuery::execute($params, new FbQueryMotTest(), $optionalMotTestTypes);

        if ($result->getResultCount() == 0 && $this->checkIfParamsNeedStripping($params)) {
            $result = SuperSearchQuery::execute($this->stripParams($params), new FbQueryMotTest(), $optionalMotTestTypes);
        }
        
        return $result;
    }


    /**
     * Search for MOT tests Log.
     *
     * @param MotTestSearchParam $params
     *
     * @return array
     * @throws \UnexpectedValueException
     */
    public function findTestsLog(MotTestSearchParam $params)
    {
        $this->authService->assertGrantedAtOrganisation(
            PermissionAtOrganisation::MOT_TEST_LIST_AT_AE, $params->getOrganisationId()
        );

        return SuperSearchQuery::execute($params, new FbQueryMotTestLog());
    }

    /**
     * Search for a site's MOT test logs.
     *
     * @param MotTestSearchParam $params
     *
     * @return array
     * @throws \UnexpectedValueException
     */
    public function findSiteTestsLog(MotTestSearchParam $params)
    {
        $this->authService->assertGrantedAtSite(
            PermissionAtSite::VTS_TEST_LOGS, $params->getSiteId()
        );

        //in order to find only tests for current AE->VTS association we need to pass organisation id
        $site = $this->siteRepository->get($params->getSiteId());
        $organisation = $site->getOrganisation();

        if(is_object($organisation)) {
            $params->setOrganisationId($organisation->getId());
            return SuperSearchQuery::execute($params, new FbQueryMotTestLog());
        } else {
            $resultDto = new SearchResultDto();
            $resultDto
                ->setSearched($params->toDto())
                ->setResultCount(0)
                ->setTotalResultCount(0)
                ->setIsElasticSearch(false);
            return $resultDto;
        }
    }

    /**
     * Search for MOT tests Log for current user
     *
     * @param MotTestSearchParam $params
     *
     * @return array
     * @throws \UnexpectedValueException
     */
    public function findTesterTestsLog(MotTestSearchParam $params)
    {
        return SuperSearchQuery::execute($params, new FbQueryMotTestLog());
    }

    /**
     * Provides the ability to check the users access to the current search
     *
     * @param string $permission
     */
    protected function checkPermissions($permission)
    {
        $this->authService->assertGranted($permission);
    }

    /**
     * Strip vin and/or registration number if set to perform extra search
     * @param SearchParam $params
     *
     * @return VehicleSearchParam
     */
    protected function stripParams(SearchParam $params)
    {
        $strippedParams = clone($params);

        if($params->getVin() != NULL && strpos($params->getVin(), " ") !== FALSE) {
            $strippedParams->setVin(preg_replace('/\s+/', '', $params->getVin()));
        }

        if($params->getRegistration() != NULL && strpos($params->getRegistration(), " ") !== FALSE) {
            $strippedParams->setRegistration(preg_replace('/\s+/', '', $params->getRegistration()));
        }

        return $strippedParams;
    }

    /**
     * @param SearchParam $params
     * @return bool
     */
    protected function checkIfParamsNeedStripping(SearchParam $params)
    {
        if($params->getVin() != NULL && strpos($params->getVin(), " ") !== FALSE) {
            return true;
        }
        if($params->getRegistration() != NULL && strpos($params->getRegistration(), " ") !== FALSE) {
            return true;
        }
    }
}
