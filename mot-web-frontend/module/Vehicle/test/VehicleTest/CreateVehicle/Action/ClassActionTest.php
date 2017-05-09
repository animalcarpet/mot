<?php

namespace VehicleTest\CreateVehicle\Action;

use Core\Action\ViewActionResult;
use Vehicle\CreateVehicle\Action\ClassAction;
use Vehicle\CreateVehicle\Controller\ColourController;
use Vehicle\CreateVehicle\Controller\EngineController;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Form\ClassForm;
use Vehicle\CreateVehicle\Service\CreateNewVehicleService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use Zend\Http\Request;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Stdlib\ParametersInterface;
use Core\Action\RedirectToRoute;
use Zend\View\Model\ViewModel;

class ClassActionTest extends \PHPUnit_Framework_TestCase
{
    const VALID_CLASS = '5';
    const INVALID_CLASS = '9';
    const EMPTY_CLASS = '';

    private $authorisationService;
    private $createVehicleStepService;
    private $request;
    private $form;
    private $createNewVehicleService;

    public function setUp()
    {
        parent::setUp();
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
        $this->request = XMock::of(Request::class);
        $this->form = XMock::of(ClassForm::class);
        $this->createNewVehicleService = XMock::of(CreateNewVehicleService::class);
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     * @expectedExceptionMessage Not allowed
     */
    public function testWhenDontHavePermission_ThenExceptionWillBeThrown()
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

    public function testWhenNotAllowedOnStep_IsRedirectedToEnginePage()
    {
        $this->isAllowedOnCurrentStep(false);
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame(EngineController::ROUTE, $actual->getRouteName());
    }

    public function testWhenGetAndPermissionToViewStep_shouldDisplayCorrectTemplate()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(false, []);
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame(ClassAction::CREATE_VEHICLE_CLASS_TEMPLATE, $actual->getTemplate());
    }

    public function testWhenPostClassSelected_shouldRedirectToNextPage()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData(self::VALID_CLASS));
        $this->mockGetAuthorisedClassesForUserAndVTS($this->mockAllowedClasses());
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame(ColourController::ROUTE, $actual->getRouteName());
    }

    public function testWhenPostNoClassSelected_shouldRemainOnPageWithErrors()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData(self::EMPTY_CLASS));
        $this->mockGetAuthorisedClassesForUserAndVTS($this->mockAllowedClasses());
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame(ClassAction::CREATE_VEHICLE_CLASS_TEMPLATE, $actual->getTemplate());
        /** @var ViewModel $viewModel */
        $viewModel = $actual->getViewModel();
        $form = $viewModel->getVariable('form');
        $this->assertCount(1, $form->getErrorMessages());
        $this->assertSame(ClassForm::SELECT_CLASS_ERROR, $form->getErrorMessages()[0]);
    }

    public function testInvalidPostData()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockGetAuthorisedClassesForUserAndVTS($this->mockAllowedClasses());
        $this->mockIsPost(true, $this->mockPostData(self::INVALID_CLASS));
        $actual = $this->buildAction()->execute($this->request);
        /** @var ViewModel $viewModel */
        $viewModel = $actual->getViewModel();
        $form = $viewModel->getVariable('form');
        $this->assertCount(1, $form->getErrorMessages());
        $this->assertSame(ClassForm::SELECT_CLASS_ERROR, $form->getErrorMessages()[0]);
    }

    public function testReturnToReview_whenUserAllowedOnReviewStage_shouldRedirectThemToReviewPage()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(true);
        $this->mockGetAuthorisedClassesForUserAndVTS($this->mockAllowedClasses());
        $this->mockIsPost(true, $this->mockPostData(self::VALID_CLASS));
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame(ReviewController::ROUTE, $actual->getRouteName());
    }

    public function testWhenPostClassSelected_testerNotAllowedToTestClass_shouldRemainOnPageWithErrors()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData(1));
        $this->mockGetAuthorisedClassesForUserAndVTS($this->mockDisallowedClasses());
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        /** @var ViewModel $viewModel */
        $viewModel = $actual->getViewModel();
        $form = $viewModel->getVariable('form');
        $this->assertCount(1, $form->getErrorMessages());
        $this->assertSame('Test class - you are not eligible to test class 1 vehicles', $form->getErrorMessages()[0]);
    }

    public function testWhenPostClassSelected_vtsNotAllowedToTestClass_shouldRemainOnPageWithErrors()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData(3));
        $this->mockGetAuthorisedClassesForUserAndVTS($this->mockDisallowedClasses());
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        /** @var ViewModel $viewModel */
        $viewModel = $actual->getViewModel();
        $form = $viewModel->getVariable('form');
        $this->assertCount(1, $form->getErrorMessages());
        $this->assertSame('Test class - this VTS is not eligible to test class 3 vehicles', $form->getErrorMessages()[0]);
    }

    private function mockPostData($class)
    {
        return ['class' => $class];
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
            ->with(CreateVehicleStepService::CLASS_STEP)
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

    private function mockGetAuthorisedClassesForUserAndVTS(array $classes)
    {
        $this->createNewVehicleService
            ->expects($this->once())
            ->method('getAuthorisedClassesForUserAndVTS')
            ->willReturn($classes);
    }

    private function mockDisallowedClasses()
    {
        return ['forPerson' => [
                1 => '2',
                2 => '3',
                3 => '4',
                4 => '5',
                5 => '7',
            ],
            'forVts' => [
                0 => '1',
                1 => '2',
                3 => '4',
                4 => '5',
                5 => '7',
            ],
        ];
    }

    private function mockAllowedClasses()
    {
        return ['forPerson' => [
                    0 => '1',
                    1 => '2',
                    2 => '3',
                    3 => '4',
                    4 => '5',
                    5 => '7',
                ],
            'forVts' => [
                    0 => '1',
                    1 => '2',
                    2 => '3',
                    3 => '4',
                    4 => '5',
                    5 => '7',
                ],
        ];
    }

    private function buildAction()
    {
        return new ClassAction(
            $this->authorisationService,
            $this->createVehicleStepService,
            $this->createNewVehicleService
        );
    }
}
