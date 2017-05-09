<?php

namespace IntegrationApi\DvlaInfo\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use IntegrationApi\DvlaInfo\Service\DvlaInfoMotHistoryService;
use Zend\View\Model\JsonModel;

class DvlaInfoMotHistoryController extends AbstractDvsaRestfulController
{
    /**
     * Returns a list of MOT Tests for given vehicle.
     * vrm and one of v5cReference or testNumber have to be provided in a query.
     *
     * @return JsonModel
     */
    public function getList()
    {
        $request = $this->getRequest();

        $vrm = $request->getQuery('vrm');
        $v5cReference = $request->getQuery('v5cReference');
        $testNumber = $request->getQuery('testNumber');

        $this->validateRequestHasParameters($vrm, $testNumber, $v5cReference);

        $motTests = $this->getDvlaInfoMotHistoryService()->getMotTests($vrm, $testNumber, $v5cReference);

        return ApiResponse::jsonOk($motTests);
    }

    /**
     * @return DvlaInfoMotHistoryService
     */
    private function getDvlaInfoMotHistoryService()
    {
        return $this->getServiceLocator()->get(DvlaInfoMotHistoryService::class);
    }

    /**
     * @param $vrm
     * @param $testNumber
     * @param $v5cReference
     */
    public function validateRequestHasParameters($vrm, $testNumber, $v5cReference)
    {
        $errors = new ErrorSchema();

        if (!$vrm) {
            $errors->add("'vrm' query parameter is required");
        }

        if (!$testNumber && !$v5cReference) {
            $errors->add("one of 'testNumber' or 'v5cReference' query parameters is required");
        }

        $errors->throwIfAny();
    }
}
