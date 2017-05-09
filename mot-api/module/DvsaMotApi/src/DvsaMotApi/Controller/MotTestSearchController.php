<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;

/**
 * Class MotTestSearchController.
 */
class MotTestSearchController extends AbstractDvsaRestfulController
{
    /**
     * Search for MotTests by site number, tester id or tester username.
     */
    public function getTestsAction()
    {
        try {
            $params = $this->buildSearchParams();

            $result = $this->getService()->findTests($params);

            return ApiResponse::jsonOk($result);
        } catch (\UnexpectedValueException $e) {
            return $this->returnBadRequestResponseModel(
                $e->getMessage(),
                self::ERROR_CODE_REQUIRED,
                $e->getMessage()
            );
        }
    }

    /**
     * Build the search params from the current request.
     *
     * @return mixed
     */
    protected function buildSearchParams()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $searchParams = new MotTestSearchParam($this->getEntityManager());

        if ($request->isPost()) {
            $postData = $this->processBodyContent($request);

            $searchParamsDto = DtoHydrator::jsonToDto($postData);

            $searchParams->fromDto($searchParamsDto);
        } else {
            $organisationId = $request->getQuery(SearchParamConst::ORGANISATION_ID);
            $testerId = $request->getQuery(SearchParamConst::SEARCH_TESTER_ID_QUERY_PARAM);
            $siteNumber = $request->getQuery(searchParamConst::SEARCH_SITE_NUMBER_QUERY_PARAM);
            $testNumber = $request->getQuery(SearchParamConst::SEARCH_TEST_NUMBER_QUERY_PARAM);
            $searchRecent = $request->getQuery(searchParamConst::SEARCH_SEARCH_RECENT_QUERY_PARAM);
            $vrm = $request->getQuery(searchParamConst::SEARCH_VRM_QUERY_PARAM);
            $vin = $request->getQuery(searchParamConst::SEARCH_VIN_QUERY_PARAM);
            $vehicleId = (int) $request->getQuery(searchParamConst::SEARCH_VEHICLE_ID_QUERY_PARAM);

            $dateFrom = $request->getQuery(searchParamConst::SEARCH_DATE_FROM_QUERY_PARAM, 0);
            $dateTo = $request->getQuery(searchParamConst::SEARCH_DATE_TO_QUERY_PARAM, time());
            $searchFilter = $request->getQuery(searchParamConst::SEARCH_SEARCH_FILTER);

            $dateFrom = (new \DateTime())->setTimestamp($dateFrom);
            $dateTo = (new \DateTime())->setTimestamp($dateTo);

            $searchParams
                ->setOrganisationId($organisationId)
                ->setSiteNumber($siteNumber)
                ->setTestNumber($testNumber)
                ->setTesterId($testerId)
                ->setSearchRecent($searchRecent)
                ->setVehicleId($vehicleId)
                ->setRegistration($vrm)
                ->setVin($vin)
                ->setDateFrom($dateFrom)
                ->setDateTo($dateTo)
                ->setSearchFilter($searchFilter)
                ->loadStandardDataTableValuesFromRequest($request);
        }

        $searchParams->process();

        return $searchParams;
    }

    /**
     * @return \DvsaElasticSearch\Service\ElasticSearchService
     */
    protected function getService()
    {
        return $this->getServiceLocator()->get('ElasticSearchService');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }
}
