<?php

namespace SiteApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\DefaultBrakeTestsService;

/**
 * Controller used for saving a site's brake test defaults.
 */
class DefaultBrakeTestsController extends AbstractDvsaRestfulController
{
    public function update($id, $data)
    {
        $service = $this->getDefaultBrakeTestsService();
        $service->put($id, $data);

        return ApiResponse::jsonOk();
    }

    /**
     * @return DefaultBrakeTestsService
     */
    private function getDefaultBrakeTestsService()
    {
        return $this->getServiceLocator()->get(DefaultBrakeTestsService::class);
    }
}
