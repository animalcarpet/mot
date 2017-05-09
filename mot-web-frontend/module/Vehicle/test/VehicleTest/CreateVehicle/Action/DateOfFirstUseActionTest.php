<?php

namespace VehicleTest\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\CreateVehicle\Action\DateOfFirstUseAction;
use Vehicle\CreateVehicle\Form\DateOfFirstUseForm;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\Stdlib\ParametersInterface;

class DateOfFirstUseActionTest extends \PHPUnit_Framework_TestCase
{
    private $createVehicleStepService;
    private $request;
    private $authorisationService;

    public function setUp()
    {
        parent::setUp();

        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
        $this->request = XMock::of(Request::class);
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     * @expectedExceptionMessage Not allowed
     */
    public function testWhenArrivingOnPage_whenPermissionNotInSystem_thenExceptionWillBeThrown()
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

    public function testWhenArrivingOnPage_whenPermissionInSystem_thenDateOfFirstUseActionIsDisplayed()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->withPermission();
        $this->mockIsPost(false, []);
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/dateOfFirstUse.twig', $actual->getTemplate());
    }

    public function testWhenPostDateIsValid_thenStepIsUpdatedAndRedirectedToReviewPage()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData('12', '12', '2015'));
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertSame('create-vehicle/new-vehicle-review', $actual->getRouteName());
    }

    public function testWhenPostDataIsInvalid_thenValidationWillFailAndWillRemainOnDatePage()
    {
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);
        $this->mockIsPost(true, $this->mockPostData('', '', ''));
        $actual = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/dateOfFirstUse.twig', $actual->getTemplate());
    }

    private function mockPostData($day, $month, $year)
    {
        return [
            DateOfFirstUseForm::FIELD_DAY => $day,
            DateOfFirstUseForm::FIELD_MONTH => $month,
            DateOfFirstUseForm::FIELD_YEAR => $year,
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
            ->with(CreateVehicleStepService::DATE_STEP)
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
        return new DateOfFirstUseAction($this->authorisationService, $this->createVehicleStepService);
    }
}
