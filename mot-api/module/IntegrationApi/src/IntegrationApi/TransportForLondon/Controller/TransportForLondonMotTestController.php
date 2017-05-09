<?php

namespace IntegrationApi\TransportForLondon\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use IntegrationApi\TransportForLondon\Service\TransportForLondonMotTestService;
use Zend\View\Model\JsonModel;

/**
 * Class TransportForLondonMotTestController.
 */
class TransportForLondonMotTestController extends AbstractDvsaRestfulController
{
    /**
 * Returns MOT Test data with additional flags (expiredWarning, laterTestInScope and laterTestOutScope).
 *
 * @return JsonModel
 */
    //TODO PT: confirm identity, roles, permissions?
    public function getList()
    {
        $request = $this->getRequest();

        $vrm = $request->getQuery('vrm');
        $v5c = $request->getQuery('v5c');

        $errors = new ErrorSchema();
        if (!$vrm) {
            $errors->add("'vrm' query parameter is required");
        }

        if (!$v5c) {
            $errors->add("'v5c' query parameter is required");
        }

        $errors->throwIfAny();

        $vehicleTestInfo = $this->getMotTestService()->getMotTest($vrm, $v5c);

        return ApiResponse::jsonOk($vehicleTestInfo);
    }

    /**
     * @return TransportForLondonMotTestService
     */
    private function getMotTestService()
    {
        return $this->getServiceLocator()->get(TransportForLondonMotTestService::class);
    }
}
