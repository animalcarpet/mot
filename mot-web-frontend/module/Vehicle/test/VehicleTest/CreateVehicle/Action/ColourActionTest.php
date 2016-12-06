<?php

namespace VehicleTest\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\CreateVehicle\Action\ColourAction;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Form\ColourForm;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Model\ViewModel;

class ColourActionTest extends \PHPUnit_Framework_TestCase
{
    private $createVehicleStepService;
    private $request;
    private $form;
    private $authorisationService;

    public function setUp()
    {
        parent::setUp();

        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
        $this->request = XMock::of(Request::class);
        $this->form = XMock::of(ColourForm::class);
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
    }

    /** @expectedException \DvsaCommon\Exception\UnauthorisedException
     *  @expectedExceptionMessage Not allowed */
    public function testWhenArrivingOnPage_whenPermissionNotInSystem_ThenExceptionWillBeThrown()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START)
            ->willThrowException(new UnauthorisedException("Not allowed"));

        $this->buildAction()->execute($this->request);
    }

    public function testReturnToReview_whenUserAllowedOnReviewStage_shouldRedirectThemToReviewPage()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START);

        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(true);
        $this->mockIsPost(true, $this->mockPostData('S', ''));
        $this->mockStaticColourData();
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame(ReviewController::ROUTE, $actual->getRouteName());
    }

    public function testWhenArrivingOnPage_whenPermissionInSystem_ThenMakeActionPageIsDisplayed()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START);

        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(false, []);
        $this->mockStaticColourData();
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/colour.twig', $actual->getTemplate());
    }

    public function testWhenUpdatingSteps_whenPostDataIsInvalid_thenValidationShouldFail()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START);

        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData('PLEASE_SELECT', ''));
        $this->mockStaticColourData();
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/colour.twig', $actual->getTemplate());
        /** @var ViewModel $viewModel */
        $viewModel = $actual->getViewModel();
        $form = $viewModel->getVariable('form');
        $this->assertCount(1, $form->getMessages());
        $this->assertSame('Select an option', $form->getMessages()['primaryColour'][0]);
    }

    public function testWhenUpdatingSteps_whenPostDataIsValid_thenStepIsUpdatedAndRedirected()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START);

        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData('S', ''));
        $this->mockStaticColourData();
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame('create-vehicle/new-vehicle-country-of-reg', $actual->getRouteName());
    }

    private function mockStaticColourData()
    {
        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStaticData')
            ->willReturn(['colour' => $this->mockAvailableColours()]);
    }

    private function mockPostData
    (
        $primaryColour,
        $secondaryColour
    )
    {
        return [
            'primaryColour' => $primaryColour,
            'secondaryColours' => $secondaryColour,
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
            ->with(CreateVehicleStepService::COLOUR_STEP)
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

    private function mockAvailableColours()
    {
        return [
            'S' => 'Beige',
            'P' => 'Black',
            'B' => 'Bronze',
            'A' => 'Brown',
            'V' => 'Cream',
            'G' => 'Gold',
            'H' => 'Green',
            'L' => 'Grey',
            'T' => 'Maroon',
            'K' => 'Purple',
            'E' => 'Orange',
            'D' => 'Pink',
            'C' => 'Red',
            'M' => 'Silver',
            'U' => 'Turquoise',
            'N' => 'White',
            'F' => 'Yellow',
            'R' => 'Multi-colour',
            'W' => 'Not Stated',
            'J' => 'Blue',
        ];
    }

    private function buildAction()
    {
        return new ColourAction(
            $this->authorisationService,
            $this->createVehicleStepService
        );
    }
}