<?php

namespace VehicleTest\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\CreateVehicle\Action\MakeAction;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\Stdlib\ParametersInterface;

class MakeActionTest extends \PHPUnit_Framework_TestCase
{
    private $authorisationService;
    private $createVehicleStepService;
    private $request;

    public function setUp()
    {
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
        $this->request = XMock::of(Request::class);
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     * @expectedExceptionMessage Not allowed
     */
    public function testWhenArrivingOnPage_whenPermissionNotInSystem_ThenExceptionWillBeThrown()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START)
            ->willThrowException(new UnauthorisedException('Not allowed'));

        $this->createVehicleStepService
            ->expects($this->never())
            ->method('getStaticData');

        $this->createVehicleStepService
            ->expects($this->never())
            ->method('getStep');

        $this->buildAction()->execute(new Request());
    }

    public function testWhenArrivingOnPage_whenPermissionInSystem_ThenMakeActionPageIsDisplayed()
    {
        $this->withPermission();
        $this->withStaticData();
        $this->withPermissionToBeOnStep();

        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStep')
            ->with(CreateVehicleStepService::MAKE_STEP)
            ->willReturn(null);

        $actual = $this->buildAction()->execute(new Request());

        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/make.twig', $actual->getTemplate());
    }

    public function testWhenUpdatingSteps_whenPostDataIsInvalid_ThenValidationShouldFail()
    {
        $this->mockIsPost(true, ['fdf']);
        $this->withPermission();
        $this->withPermissionToBeOnStep();
        $this->withStaticData();

        $actual = $this->buildAction()->execute($this->request);
        $this->assertSame('vehicle/create-vehicle/make.twig', $actual->getTemplate());
    }

    public function testWhenUpdatingSteps_whenPostDataIsValid_ThenStepIsUpdatedAndRedirected()
    {
        $this->withPermissionToBeOnStep();
        $this->mockIsPost(true, $this->getMockPostData());
        $this->withPermission();
        $this->withStaticData();

        $redirect = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $redirect);
    }

    private function getMockedSampleData()
    {
        return ['make' => [['id' => 'HN123', 'name' => 'Honda'], ['id' => 'HN123', 'name' => 'Honda']]];
    }

    private function getMockPostData()
    {
        return [
            'vehicleMake' => 'Other',
            'Other' => 'Batmobile',
        ];
    }

    private function withStaticData()
    {
        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStaticData')
            ->willReturn($this->getMockedSampleData());
    }

    private function withPermissionToBeOnStep()
    {
        $this->createVehicleStepService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with(CreateVehicleStepService::MAKE_STEP)
            ->willReturn(true);
    }

    private function withPermission()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START)
            ->wilLReturn(true);
    }

    private function buildAction()
    {
        return new MakeAction(
            $this->authorisationService,
            $this->createVehicleStepService
        );
    }

    private function mockIsPost($isPost, $postData)
    {
        if ($isPost) {
            $params = XMock::of(ParametersInterface::class);
            $params->expects($this->once())
                ->method('toArray')
                ->willReturn($postData);

            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
            $this->request->expects($this->once())->method('getPost')->willReturn($params);
        } else {
            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
        }
    }
}
