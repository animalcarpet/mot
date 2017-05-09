<?php

namespace VehicleApiTest\Controller;

use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use DvsaMotApiTest\Controller\AbstractMotApiControllerTestCase;
use VehicleApi\Controller\VehicleRetestEligibilityController;
use Zend\Http\Request;

/**
 * Unit tests for VehicleRetestEligibilityController.
 */
class VehicleRetestEligibilityControllerTest extends AbstractMotApiControllerTestCase
{
    const CONTINGENCY_TEST = true;

    protected function setUp()
    {
        parent::setUp();

        $retestEligibilityValidatorMock = $this->createRetestEligibilityValidatorMockService();
        $this->controller = new VehicleRetestEligibilityController($retestEligibilityValidatorMock);
        $this->setUpController($this->controller);
    }

    public function testCreateEligibilityWithValidData()
    {
        $this->runTestCreateEligibilityWithValidData();
    }

    public function testCreateEligibilityWithValidDataAndContingency()
    {
        $this->runTestCreateEligibilityWithValidData(self::CONTINGENCY_TEST);
    }

    private function runTestCreateEligibilityWithValidData($isContingencyPostParamSent = false)
    {
        if (false === $isContingencyPostParamSent) {
            $this->request->getPost()->set(
                VehicleRetestEligibilityController::FIELD_CONTINGENCY_DTO,
                [
                    '_class' => ContingencyTestDto::class,
                ]
            );
        }

        $result = $this->getResultForAction(
            Request::METHOD_POST,
            null,
            [
                VehicleRetestEligibilityController::FIELD_VEHICLE_ID => 3,
                VehicleRetestEligibilityController::FIELD_SITE_ID => 1,
            ]
        );
        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => ['isEligible' => true]], $result);
    }

    /**
     * @throws \Exception
     *
     * @return RetestEligibilityValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRetestEligibilityValidatorMockService()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|RetestEligibilityValidator $mockService */
        $mockService = XMock::of(RetestEligibilityValidator::class, ['checkEligibilityForRetest']);
        $mockService->expects($this->once())
            ->method('checkEligibilityForRetest')
            ->willReturn(true);

        $this->serviceManager->setService(RetestEligibilityValidator::class, $mockService);

        return $mockService;
    }
}
