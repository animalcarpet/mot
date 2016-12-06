<?php

namespace VehicleTest\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\CreateVehicle\Action\ModelAction;
use Vehicle\CreateVehicle\Form\ModelForm;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\Stdlib\ParametersInterface;
use Vehicle\CreateVehicle\Service\CreateVehicleModelService;

class ModelActionTest extends \PHPUnit_Framework_TestCase
{
    private $authorisationService;
    private $createVehicleStepService;
    private $createVehicleModelService;
    private $request;

    public function setUp()
    {
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
        $this->createVehicleModelService = XMock::of(CreateVehicleModelService::class);
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
            ->willThrowException(new UnauthorisedException("Not allowed"));

        $this->buildAction()->execute(new Request());
    }

    public function testWhenArrivingOnPage_whenPermissionInSystem_ThenMakeActionPageIsDisplayed()
    {
        $this->withPermission();
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->withModelFromMakeInSession();

        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStep')
            ->with(CreateVehicleStepService::MODEL_STEP)
            ->willReturn(null);

        $actual = $this->buildAction()->execute(new Request());

        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/model.twig', $actual->getTemplate());
    }

    public function testWhenUpdatingSteps_whenPostDataIsInvalid_ThenValidationShouldFail()
    {
        $this->withPermission();
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, ['fdf']);
        $this->withModelFromMakeInSession();

        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStep')
            ->with(CreateVehicleStepService::MODEL_STEP)
            ->willReturn(null);

        $actual = $this->buildAction()->execute($this->request);

        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/model.twig', $actual->getTemplate());
    }

    public function testReturnToReview_whenUserAllowedOnReviewStage_shouldRedirectThemToReviewPage()
    {
        $this->withPermission();
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(true);
        $this->mockIsPost(true, $this->getMockPostData());
        $this->withModelFromMakeInSession();


        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStep')
            ->with(CreateVehicleStepService::MODEL_STEP)
            ->willReturn(null);

        $redirect = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $redirect);
    }

    public function testWhenUpdatingSteps_whenPostDataIsValid_ThenStepIsUpdatedAndRedirected()
    {
        $this->withPermission();
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->getMockPostData());
        $this->withModelFromMakeInSession();


        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStep')
            ->with(CreateVehicleStepService::MODEL_STEP)
            ->willReturn(null);

        $redirect = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $redirect);
    }

    private function getMockPostData()
    {
        return [
            ModelForm::MODEL => 'Other',
            ModelForm::OTHER => 'CarModel'
        ];
    }

    private function withPermission()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START)
            ->wilLReturn(true);
    }

    private function isAllowedOnCurrentStep($isAllowed)
    {
        $this->createVehicleStepService
            ->expects($this->at(0))
            ->method('isAllowedOnStep')
            ->with(CreateVehicleStepService::MODEL_STEP)
            ->willReturn($isAllowed);
    }

    private function isAllowedOnReviewStep($isAllowed)
    {
        $this->createVehicleStepService
            ->expects($this->at(1))
            ->method('isAllowedOnStep')
            ->with(CreateVehicleStepService::REVIEW_STEP)
            ->willReturn($isAllowed);
    }

    private function withModelFromMakeInSession()
    {
        $this->createVehicleModelService
            ->expects($this->once())
            ->method('getModelFromMakeInSession')
            ->willReturn([]);
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

    private function buildAction()
    {
        return new ModelAction(
            $this->authorisationService,
            $this->createVehicleStepService,
            $this->createVehicleModelService
        );
    }
}