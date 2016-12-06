<?php

namespace VehicleTest\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\CreateVehicle\Action\EngineAction;
use Vehicle\CreateVehicle\Form\EngineForm;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\Stdlib\ParametersInterface;

class EngineActionTest extends \PHPUnit_Framework_TestCase
{
    const ERROR_CAPACITY_REQUIRED = 'Enter a value';
    private $createVehicleStepService;
    private $request;
    private $form;

    public function setUp()
    {
        parent::setUp();

        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
        $this->request = XMock::of(Request::class);
        $this->form = XMock::of(EngineForm::class);
    }

    public function testWhenGet_permissionToViewStep_shouldDisplayCorrectTemplate()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(false, []);
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/engine.twig', $actual->getTemplate());
    }

    public function testWhenPost_permissionToViewStep_formValid_shouldRedirectToNextPage()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData(FuelTypeCode::PETROL, '1400'));
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame('create-vehicle/new-vehicle-class', $actual->getRouteName());
    }

    public function testWhenPost_permissionToViewStep_formInValid_shouldRemainOnPageWithErrors()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData(FuelTypeCode::PETROL, ''));
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/engine.twig', $actual->getTemplate());

        $viewModel = $actual->getViewModel();
        $form = $viewModel->getVariable('form');
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_CAPACITY_REQUIRED, $form->getMessages()[EngineForm::FIELD_CAPACITY][0]);
    }

    public function testReturnToReview_whenUserAllowedOnReviewStage_shouldRedirectThemToReviewPage()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(true);
        $this->mockIsPost(true, $this->mockPostData(FuelTypeCode::PETROL, '1400'));
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame('create-vehicle/new-vehicle-review', $actual->getRouteName());
    }

    private function mockPostData($fuelType, $capacity)
    {
        return [
            EngineForm::FIELD_FUEL_TYPE => $fuelType,
            EngineForm::FIELD_CAPACITY => $capacity
        ];
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

    private function isAllowedOnCurrentStep($isAllowed)
    {
        $this->createVehicleStepService
            ->expects($this->at(0))
            ->method('isAllowedOnStep')
            ->with(CreateVehicleStepService::ENGINE_STEP)
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

    private function buildAction()
    {
        return new EngineAction($this->createVehicleStepService);
    }
}