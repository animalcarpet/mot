<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaMotApi\Service\BrakeTestResultService;
use DvsaMotApi\Service\MotTestService;

class MotTestBrakeTestConfigurationValidationController extends AbstractDvsaRestfulController
{
    public function create($data)
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber', null);

        /**
         * @var MotTestService
         */
        $motTestService = $this->getServiceLocator()->get('MotTestService');

        $motTest = $motTestService->getMotTest($motTestNumber);

        /**
         * @var BrakeTestResultService
         */
        $brakeTestResultService = $this->getServiceLocator()->get('BrakeTestResultService');

        $brakeTestResultService->validateBrakeTestConfiguration($motTest, $data);

        return ApiResponse::jsonOk(['valid' => true]);
    }
}
