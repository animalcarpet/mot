<?php

namespace SiteApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\SiteService;

/**
 * Class VehicleTestingStationAuthorisedClassesController.
 */
class VehicleTestingStationAuthorisedClassesController extends AbstractDvsaRestfulController
{
    public function get($vtsId)
    {
        $authorisedClasses = $this->getSiteService()->getSiteAuthorisedClasses($vtsId);

        return ApiResponse::jsonOk($authorisedClasses);
    }

    /**
     * @return SiteService
     */
    private function getSiteService()
    {
        return $this->getServiceLocator()->get(SiteService::class);
    }
}
