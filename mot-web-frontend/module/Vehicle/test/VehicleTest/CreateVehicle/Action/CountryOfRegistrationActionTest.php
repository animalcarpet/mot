<?php

namespace VehicleTest\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\CreateVehicle\Action\CountryOfRegistrationAction;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\Http\Request;
use Zend\Stdlib\ParametersInterface;

class CountryOfRegistrationActionTest extends \PHPUnit_Framework_TestCase
{
    private $createVehicleStepService;

    private $request;

    private $authorisationService;

    public function setUp()
    {
        parent::setUp();

        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
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

        $this->buildAction()->execute(new Request());
    }

    public function testReturnToReview_whenUserAllowedOnReviewStage_shouldRedirectThemToReviewPage()
    {
        $this->withPermission();
        $this->mockIsPost(true, $this->mockPostData('UK'));
        $this->getCountries();
        $this->mockGetStepInformation();
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(true);

        $this->createVehicleStepService
            ->expects($this->once())
            ->method('saveStep')
            ->with(CreateVehicleStepService::COUNTRY_STEP, $this->mockPostData('UK'));

        $this->createVehicleStepService
            ->expects($this->once())
            ->method('updateStepStatus')
            ->with(CreateVehicleStepService::COUNTRY_STEP, true);

        $redirect = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $redirect);
        $this->assertSame(ReviewController::ROUTE, $redirect->getRouteName());
    }

    public function testWhenArrivingOnPage_whenPermissionInSystem_ThenMakeActionPageIsDisplayed()
    {
        $this->withPermission();
        $this->mockGetStepInformation();
        $this->getCountries();
        $this->mockGetStepInformation();
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);

        $actual = $this->buildAction()->execute(new Request());

        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/countryOfRegistration.twig', $actual->getTemplate());
    }

    public function testWhenUpdatingSteps_whenPostDataIsInvalid_ThenValidationShouldFail()
    {
        $this->withPermission();
        $this->mockIsPost(true, $this->mockPostData(''));
        $this->getCountries();
        $this->mockGetStepInformation();
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);

        $actual = $this->buildAction()->execute($this->request);

        $this->assertInstanceOf(ViewActionResult::class, $actual);
        $this->assertSame('vehicle/create-vehicle/countryOfRegistration.twig', $actual->getTemplate());
    }

    public function testWhenUpdatingSteps_whenPostDataIsValid_ThenStepIsUpdatedAndRedirected()
    {
        $this->withPermission();
        $this->mockIsPost(true, $this->mockPostData('UK'));
        $this->getCountries();
        $this->mockGetStepInformation();
        $this->isAllowedOnCurrentStep(true);
        $this->isAllowedOnReviewStep(false);

        $this->createVehicleStepService
            ->expects($this->once())
            ->method('saveStep')
            ->with(CreateVehicleStepService::COUNTRY_STEP, $this->mockPostData('UK'));

        $this->createVehicleStepService
            ->expects($this->once())
            ->method('updateStepStatus')
            ->with(CreateVehicleStepService::COUNTRY_STEP, true);

        $redirect = $this->buildAction()->execute($this->request);
        $this->assertInstanceOf(RedirectToRoute::class, $redirect);
    }

    private function mockPostData($countryOfRegistration)
    {
        return [
            'countryOfRegistration' => $countryOfRegistration,
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

    public function withPermission()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_START)
            ->willReturn(true);
    }

    private function isAllowedOnStep($isAllowed)
    {
        $this->createVehicleStepService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with(CreateVehicleStepService::COUNTRY_STEP)
            ->willReturn($isAllowed);
    }

    private function getCountries()
    {
        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStaticData')
            ->willReturn($this->mockedCountriesData());
    }

    private function isAllowedOnCurrentStep($isAllowed)
    {
        $this->createVehicleStepService
            ->expects($this->at(0))
            ->method('isAllowedOnStep')
            ->with(CreateVehicleStepService::COUNTRY_STEP)
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

    private function mockedCountriesData()
    {
        return [
            CreateVehicleStepService::COUNTRY_STEP => [
                    ['code' => 'UK', 'name' => 'United Kingdom'],
                    ['code' => 'IRELAND', 'name' => 'Republic of Ireland'],
                ],
        ];
    }

    private function mockGetStepInformation(array $stepData = null)
    {
        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStep')
            ->with(CreateVehicleStepService::COUNTRY_STEP)
            ->willReturn($stepData);
    }

    private function buildAction()
    {
        return new CountryOfRegistrationAction(
            $this->createVehicleStepService,
            $this->authorisationService
        );
    }
}
