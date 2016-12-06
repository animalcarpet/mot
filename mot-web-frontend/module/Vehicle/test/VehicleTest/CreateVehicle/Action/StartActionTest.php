<?php

namespace VehicleTest\CreateVehicle\Action;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\CreateVehicle\Action\StartAction;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;

class StartActionTest extends \PHPUnit_Framework_TestCase
{
    private $authorisationService;
    private $createVehicleStepService;

    public function setUp()
    {
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
    }

    /** @expectedException \DvsaCommon\Exception\UnauthorisedException
     *  @expectedExceptionMessage Not allowed */
    public function testWhenGet_whenPermissionNotInSystem_willThrowUnauthorisedException()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START)
            ->willThrowException(new UnauthorisedException("Not allowed"));

        $this->createVehicleStepService
            ->expects($this->never())
            ->method('loadStepsIntoSession');

        $this->buildAction()->execute();
    }

    public function testWhenGet_whenPermissionInSystem_willDisplayCreateVehiclePage()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START);

        $this->createVehicleStepService
            ->expects($this->once())
            ->method('loadStepsIntoSession');

        $actual = $this->buildAction()->execute();

        $this->assertSame('vehicle/create-vehicle/start.twig', $actual->getTemplate());
    }

    private function buildAction()
    {
        return new StartAction(
            $this->authorisationService,
            $this->createVehicleStepService
        );
    }
}