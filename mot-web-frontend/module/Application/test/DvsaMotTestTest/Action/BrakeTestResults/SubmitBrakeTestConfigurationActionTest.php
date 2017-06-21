<?php

namespace DvsaMotTestTest\Action\BrakeTestResults;

use Core\Action\RedirectToRoute;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Messages\InvalidTestStatus;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Action\BrakeTestResults\SubmitBrakeTestConfigurationAction;
use DvsaMotTest\Controller\BrakeTestResultsController;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Helper\BrakeTestConfigurationContainerHelper;
use DvsaMotTest\Service\BrakeTestConfigurationService;
use DvsaMotTestTest\TestHelper\Fixture;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

class SubmitBrakeTestConfigurationActionTest extends TestCase
{
    /** @var BrakeTestConfigurationService|MockObject $brakeTestConfigurationService */
    private $brakeTestConfigurationService;

    /** @var stdClass $motTestData */
    private $motTestData;

    public function setUp()
    {
        $this->brakeTestConfigurationService = XMock::of(BrakeTestConfigurationService::class);

        $this->motTestData = new stdClass();
        $this->motTestData->motTestNumber = 295116285800;
        $this->motTestData->vehicleId = 1;
        $this->motTestData->vehicleVersion = 1;
        $this->motTestData->status = MotTestStatusName::ACTIVE;
    }

    public function testRedirectToResultsOnSuccess()
    {
        $action = $this->buildAction();

        /** @var RedirectToRoute $actionResult */
        $actionResult = $action->execute([], 1);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertEquals(
            BrakeTestResultsController::ROUTE_MOT_TEST_BRAKE_TEST_RESULTS,
            $actionResult->getRouteName()
        );
    }

    public function testValidateWeightType()
    {
        $this->brakeTestConfigurationService
            ->expects($this->once())
            ->method('validateConfiguration')
            ->willThrowException(new ValidationException(" ", "POST", [], 400,
                [[  "message" =>"Please choose vehicle weight type",
                    "code" => 60,
                    "displayMessage" => "Please choose vehicle weight type"]]));

        $action = $this->buildAction();
        $actionResult = $action->execute([],1);

        $this->assertContains("Please choose vehicle weight type", $actionResult->getErrorMessages());
        $this->assertNotContains("Please enter a valid vehicle weight", $actionResult->getErrorMessages());
    }

    public function testValidateWeightValue()
    {
        $this->brakeTestConfigurationService
            ->expects($this->once())
            ->method('validateConfiguration')
            ->willThrowException(new ValidationException(" ", "POST", [], 400,
                [[  "message" =>"Please enter a valid vehicle weight",
                    "code" => 60,
                    "displayMessage" => "Please enter a valid vehicle weight"]]));

        $action = $this->buildAction();
        $actionResult = $action->execute([],1);

        $this->assertContains("Please enter a valid vehicle weight", $actionResult->getErrorMessages());
        $this->assertNotContains("Please choose vehicle weight type", $actionResult->getErrorMessages());
    }

    public function testValidateVehicleWeightAndType()
    {
        $this->brakeTestConfigurationService
            ->expects($this->once())
            ->method('validateConfiguration')
            ->willThrowException(new ValidationException(" ", "POST", [], 400,
                [[  "message" =>"Please choose vehicle weight type",
                    "code" => 60,
                    "displayMessage" => "Please choose vehicle weight type"],
                 [  "message" =>"Please enter a valid vehicle weight",
                    "code" => 60,
                    "displayMessage" => "Please enter a valid vehicle weight"]
                ]));

        $action = $this->buildAction();
        $actionResult = $action->execute([],1);

        $this->assertContains("Please enter a valid vehicle weight", $actionResult->getErrorMessages());
        $this->assertContains("Please choose vehicle weight type", $actionResult->getErrorMessages());
    }

    public function testRedirectWithErrorMessageWhenTestIsNotActive()
    {
        $this->withMotTestStatus(MotTestStatusName::PASSED);

        $action = $this->buildAction();

        /** @var RedirectToRoute $actionResult */
        $actionResult = $action->execute([], 1);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertEquals(MotTestController::ROUTE_MOT_TEST, $actionResult->getRouteName());
        $this->assertContains(
            InvalidTestStatus::ERROR_MESSAGE_TEST_COMPLETE,
            $actionResult->getErrorMessages()
        );
    }

    private function withMotTestStatus($status)
    {
        $this->motTestData->status = $status;

        return $this;
    }

    private function buildAction()
    {
        $motTestService = XMock::of(MotTestService::class);
        $motTestService
            ->expects($this->any())
            ->method('getMotTestByTestNumber')
            ->willReturn(new MotTest($this->motTestData));

        $vehicleService = XMock::of(VehicleService::class);
        $vehicleService
            ->expects($this->any())
            ->method('getDvsaVehicleByIdAndVersion')
            ->willReturn(new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true)));

        $action = new SubmitBrakeTestConfigurationAction(
            XMock::of(WebPerformMotTestAssertion::class),
            XMock::of(BrakeTestConfigurationContainerHelper::class),
            $vehicleService,
            $motTestService,
            $this->brakeTestConfigurationService
        );

        return $action;
    }
}
