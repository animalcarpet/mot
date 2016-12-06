<?php

namespace VehicleTest\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\CreateVehicle\Action\RegistrationAndVinAction;
use Vehicle\CreateVehicle\Form\RegistrationAndVinForm;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Model\ViewModel;

class RegistrationAndVinActionTest extends \PHPUnit_Framework_TestCase
{
    private $createVehicleStepService;

    private $request;

    private $form;

    public function setUp()
    {
        parent::setUp();

        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
        $this->request = XMock::of(Request::class);
        $this->form = XMock::of(RegistrationAndVinForm::class);
    }

    public function testWhenGet_permissionToViewStep_shouldDisplayCorrectTemplate()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(false, []);
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/registrationAndVin.twig', $actual->getTemplate());
    }

    public function testWhenPost_permissionToViewStep_formValid_shouldRedirectToNextPage()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData('BTEST 123', '12345', false, false));
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame('create-vehicle/new-vehicle-make', $actual->getRouteName());
    }

    public function testReturnToReview_whenUserAllowedOnReviewStage_shouldRedirectThemToReviewPage()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(true);
        $this->mockIsPost(true, $this->mockPostData('BTEST 123', '12345', false, false));
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame('create-vehicle/new-vehicle-review', $actual->getRouteName());
    }

    public function testWhenPost_permissionToViewStep_formInValid_shouldRemainOnPageWithErrors()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData('BTEST 123', '12345', true, false));
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/registrationAndVin.twig', $actual->getTemplate());
        /** @var ViewModel $viewModel */
        $viewModel = $actual->getViewModel();
        $form = $viewModel->getVariable('form');
        $this->assertCount(1, $form->getMessages());
        $this->assertSame('Either enter the registration or select ‘I can’t provide a registration mark’', $form->getMessages()['reg-input'][0]);
    }

    private function mockPostData
    (
        $regInput,
        $vinInput,
        $leavingRegBlank,
        $leavingVinBlank
    )
    {
        return [
            'reg-input' => $regInput,
            'vin-input' => $vinInput,
            'leavingRegBlank' => $leavingRegBlank,
            'leavingVINBlank' => $leavingVinBlank,
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

    private function isAllowedOnReviewStep($isAllowed)
    {
        $this->createVehicleStepService
            ->expects($this->at(1))
            ->method('isAllowedOnStep')
            ->with(CreateVehicleStepService::REVIEW_STEP)
            ->willReturn($isAllowed);
    }

    private function isAllowedOnCurrentStep($isAllowed)
    {
        $this->createVehicleStepService
            ->expects($this->at(0))
            ->method('isAllowedOnStep')
            ->with(CreateVehicleStepService::REG_VIN_STEP)
            ->willReturn($isAllowed);
    }

    private function buildAction()
    {
        return new RegistrationAndVinAction(
            $this->createVehicleStepService
        );
    }
}