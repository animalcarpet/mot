<?php

namespace SiteApi\Controller;

use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\SiteTestingFacilitiesService;

/**
 * Controller for getting / updating site's testing facilities.
 */
class SiteTestingFacilitiesController extends AbstractDvsaRestfulController
{
    /**
     * @var SiteTestingFacilitiesService
     */
    private $siteTestingFacilitiesService;

    public function __construct(SiteTestingFacilitiesService $siteTestingFacilitiesService)
    {
        $this->siteTestingFacilitiesService = $siteTestingFacilitiesService;
    }

    public function get($siteId)
    {
        $result = $this->siteTestingFacilitiesService->get($siteId);

        return ApiResponse::jsonOk($result);
    }

    public function update($siteId, $data)
    {
        /** @var VehicleTestingStationDto $dto */
        $dto = DtoHydrator::jsonToDto($data);
        $result = $this->siteTestingFacilitiesService->update($siteId, $dto);

        return ApiResponse::jsonOk($result);
    }
}
