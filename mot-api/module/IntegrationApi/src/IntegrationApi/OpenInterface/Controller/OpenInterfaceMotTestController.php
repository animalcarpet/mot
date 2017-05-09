<?php

namespace IntegrationApi\OpenInterface\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use IntegrationApi\OpenInterface\Service\OpenInterfaceMotTestService;
use Zend\View\Model\JsonModel;

/**
 * Class OpenInterfaceMotTestController.
 */
class OpenInterfaceMotTestController extends AbstractDvsaRestfulController
{
    /**
     * Returns data of a Passed test with latest in the future expiry date.
     * Test was issued before or on given day (today if no date specified).
     *
     * @return JsonModel
     */
    public function getList()
    {
        $request = $this->getRequest();
        $vrm = $request->getQuery('vrm');

        if (strlen(trim($vrm)) == 0) {
            ErrorSchema::throwError("'vrm' query parameter is required");
        }

        $date = $request->getQuery('date');

        $vehicleTestInfo = $this->getMotTestService()->getPassMotTestForVehicleIssuedBefore($vrm, $date);

        return ApiResponse::jsonOk($vehicleTestInfo);
    }

    /**
     * @return OpenInterfaceMotTestService
     */
    private function getMotTestService()
    {
        return $this->getServiceLocator()->get(OpenInterfaceMotTestService::class);
    }
}
