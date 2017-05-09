<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

/**
 * Class MotTestBrakeTestResultController.
 */
class MotTestBrakeTestResultController extends AbstractDvsaRestfulController
{
    public function __construct()
    {
        $this->addHttpMethodHandler('get', [$this, 'getMotTestDataAction']);
    }

    public function create($data)
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber', null);

        /** @var \DvsaMotApi\Service\MotTestService $motTestService */
        $motTestService = $this->getServiceLocator()->get('MotTestService');
        $motTest = $motTestService->getMotTest($motTestNumber);

        /** @var \DvsaMotApi\Service\BrakeTestResultService $brakeService */
        $brakeService = $this->getServiceLocator()->get('BrakeTestResultService');
        $brakeService->updateBrakeTestResult($motTest, $data);

        return ApiResponse::jsonOk();
    }

    public function getMotTestDataAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber', null);

        $motTestService = $this->getServiceLocator()->get('MotTestService');
        $motTestData = $motTestService->getMotTestData($motTestNumber);

        return ApiResponse::jsonOk($motTestData);
    }
}
