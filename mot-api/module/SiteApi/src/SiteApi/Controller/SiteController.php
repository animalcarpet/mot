<?php

namespace SiteApi\Controller;

use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\SiteService;

/**
 * Controller which creates/edits new VTS.
 */
class SiteController extends AbstractDvsaRestfulController
{
    const SITE_NUMBER_QUERY_PARAMETER = 'siteNumber';

    const SITE_ID_REQUIRED_MESSAGE = 'Query parameter site Id is required';
    const SITE_ID_REQUIRED_DISPLAY_MESSAGE = 'You need to enter a Site Id to perform the search';
    const SITE_NUMBER_REQUIRED_MESSAGE = 'Query parameter siteNumber is required';
    const SITE_NUMBER_REQUIRED_DISPLAY_MESSAGE = 'You need to enter a Site Number to perform the search';
    const SITE_NUMBER_INVALID_DATA_MESSAGE = 'siteNumber: non alphanumeric characters found';
    const SITE_NUMBER_INVALID_DATA_DISPLAY_MESSAGE = 'Site number should contain alphanumeric characters only';

    /**
     * @var SiteService
     */
    private $service;

    /**
     * @param SiteService $service
     */
    public function __construct(SiteService $service)
    {
        $this->service = $service;
    }

    public function create($data)
    {
        /** @var VehicleTestingStationDto $dto */
        $dto = DtoHydrator::jsonToDto($data);
        $result = $this->service->create($dto);

        return ApiResponse::jsonOk($result);
    }

    public function get($id)
    {
        $data = $this->service->getSite($id);

        return ApiResponse::jsonOk($data);
    }
}
