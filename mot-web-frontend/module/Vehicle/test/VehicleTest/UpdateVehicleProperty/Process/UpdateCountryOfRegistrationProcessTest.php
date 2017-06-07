<?php

namespace VehicleTest\UpdateVehicleProperty\Process;

use Core\Action\RedirectToRoute;
use Core\Catalog\CountryOfRegistration\CountryOfRegistrationCatalog;
use CoreTest\Service\StubCatalogService;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Enum\CountryOfRegistrationId;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaMotTest\Service\StartTestChangeService;
use stdClass;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Process\UpdateCountryOfRegistrationProcess;
use Zend\View\Helper\Url;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use DvsaCommonTest\TestUtils\XMock;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;

class UpdateCountryOfRegistrationProcessTest extends \PHPUnit_Framework_TestCase
{
    const TEST_COUNTRY_OF_REG = 'test_country_of_reg';
    const TEST_VEHICLE_ID = 129;

    /* @var UpdateCountryOfRegistrationProcess */
    private $updateCountryRegistrationProcess;

    /* @var CountryOfRegistrationCatalog */
    private $countryCatalog;

    /** @var MethodSpy */
    private $vehicleServiceUpdateSpy;

    /** @var MethodSpy */
    private $vehicleServiceGetSpy;

    /* @var Url */
    private $url;

    /* @var VehicleService */
    private $vehicleService;

    /* @var VehicleEditBreadcrumbsBuilder */
    private $breadcrumbsBuilder;

    /** @var DvsaVehicleBuilder */
    private $dvsaVehicleBuilder;

    /** @var DvsaVehicle */
    private $vehicle;

    private $obfuscatedId = 'OBF-15-SCATED';

    /** @var StartTestChangeService */
    private $startTestChangeService;

    /** @var  MotAuthorisationServiceInterface */
    private $authorisationServiceInterface;

    public function setUp()
    {
        $this->dvsaVehicleBuilder = new DvsaVehicleBuilder();

        $vehicleService = XMock::of(VehicleService::class);
        $this->vehicleServiceUpdateSpy = new MethodSpy($vehicleService, 'updateDvsaVehicleAtVersion');
        $this->vehicleServiceGetSpy = new MethodSpy($vehicleService, 'getDvsaVehicleById');
        $this->startTestChangeService = XMock::of(StartTestChangeService::class);
        $this->authorisationServiceInterface = XMock::of(MotAuthorisationServiceInterface::class);

        $this->updateCountryRegistrationProcess = new UpdateCountryOfRegistrationProcess(
            new CountryOfRegistrationCatalog(new StubCatalogService()),
            XMock::of(Url::class),
            $vehicleService,
            XMock::of(VehicleEditBreadcrumbsBuilder::class),
            $this->startTestChangeService
        );

        $this->countryCatalog = XMock::of(CountryOfRegistrationCatalog::class);
        $this->url = XMock::of(Url::class);
        $this->vehicleService = XMock::of(VehicleService::class);
        $this->breadcrumbsBuilder = XMock::of(VehicleEditBreadcrumbsBuilder::class);

        $vehicleStd = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $vehicleStd->id = 15;
        $emptyResource = new \stdClass();
        $emptyResource->id = null;
        $emptyResource->name = null;
        $vehicleStd->model = $emptyResource;
        $vehicleStd->make = $emptyResource;

        $this->vehicle = new DvsaVehicle($vehicleStd);

        $context = new UpdateVehicleContext($this->vehicle, $this->obfuscatedId, '');

        $this->updateCountryRegistrationProcess->setContext($context);
    }

    public function test_WhenGettingPrepopulatedData_ReturnsArrayWithCorrectKey()
    {
        $this->createUpdateCountryOfRegistrationProcess();

        /* @var UpdateVehicleContext $updateVehicleContext */
        $updateVehicleContext = XMock::of(UpdateVehicleContext::class);
        $updateVehicleContext->expects($this->once())
            ->method('getVehicle')
            ->willReturn($this->buildDvsaVehicle());

        $this->updateCountryRegistrationProcess->setContext($updateVehicleContext);
        $prepopulatedData = $this->updateCountryRegistrationProcess->getPrePopulatedData();

        $this->assertSame(['country-of-registration' => 321], $prepopulatedData);
    }

    public function test_WhenEditStepViewModelCalled_ThenViewModelCreatedWithCorrectUrl()
    {
        $this->createUpdateCountryOfRegistrationProcess();

        $formActionUrl = 'test_formaction_url';
        $this->url->expects($this->at(0))
            ->method('__invoke')
            ->willReturn($formActionUrl);

        $backUrl = 'test_back_url';
        $this->startTestChangeService->expects($this->once())
            ->method('vehicleExaminerReturnUrl')
            ->willReturn($backUrl);

        /* @var UpdateVehicleContext $updateVehicleContext */
        $updateVehicleContext = XMock::of(UpdateVehicleContext::class);
        $updateVehicleContext->expects($this->once())
            ->method('getVehicle')
            ->willReturn($this->createDvsaVehicle());

        $this->updateCountryRegistrationProcess->setContext($updateVehicleContext);
        $viewModel = $this->updateCountryRegistrationProcess->buildEditStepViewModel(null);

        $this->assertSame('test_back_url', $viewModel->getBackUrl());
        $this->assertSame('test_formaction_url', $viewModel->getFormActionUrl());
    }

    public function test_WhenRedirectToStart_TheRedirectRouteContainsCorrectVehicleId()
    {
        $this->createUpdateCountryOfRegistrationProcess();

        /* @var UpdateVehicleContext $updateVehicleContext */
        $updateVehicleContext = XMock::of(UpdateVehicleContext::class);
        $updateVehicleContext->expects($this->once())
            ->method('getObfuscatedVehicleId')
            ->willReturn('test_obfuscated_id_for_start');
        $this->updateCountryRegistrationProcess->setContext($updateVehicleContext);

        /* @var RedirectToRoute $redirectToRoute */
        $redirectToRoute = $this->updateCountryRegistrationProcess->redirectToStartPage();
        $this->assertSame('test_obfuscated_id_for_start', $redirectToRoute->getRouteParams()['id']);
    }

    public function testUpdate()
    {
        // WHEN the process updates vehicle's country of registration
        $countryId = 12;
        $this->updateCountryRegistrationProcess->update(['country-of-registration' => 12]);

        // THEN vehicle api service is being called
        $this->assertEquals(1, $this->vehicleServiceUpdateSpy->invocationCount());

        // for a correct vehicle
        $this->assertSame($this->getVehicle()->getId(), $this->vehicleServiceUpdateSpy->paramsForLastInvocation()[0]);

        // with correct country
        /** @var UpdateDvsaVehicleRequest $updateRequest */
        $updateRequest = $this->vehicleServiceUpdateSpy->paramsForLastInvocation()[2];
        $this->assertEquals($countryId, $updateRequest->getCountryOfRegistrationId());
    }

    public function testPrePopulatedData_vehicleExaminerChange()
    {
        // GIVEN the vehicle has a country of registration
        $countryId = 18;

        $stdClass = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $stdClass->countryOfRegistrationId = $countryId;

        $vehicle = new DvsaVehicle($stdClass);

        $this->vehicleServiceGetSpy->mock()
            ->willReturn($vehicle);
        /* @var UpdateVehicleContext $updateVehicleContext */
        $updateVehicleContext = XMock::of(UpdateVehicleContext::class);
        $updateVehicleContext->expects($this->once())
            ->method('getVehicle')
            ->willReturn($vehicle);

        $this->updateCountryRegistrationProcess->setContext($updateVehicleContext);

        // WHEN the process retrieves pre-populated data
        $data = $this->updateCountryRegistrationProcess->getPrePopulatedData();

        // THEN I can see it in the form
        $this->assertArrayHasKey('country-of-registration', $data);
        $this->assertEquals($countryId, $data['country-of-registration']);
    }

    public function testPrePopulatedData_whenNotDvlaVehicle_valuesNotChangedInSession()
    {
        // GIVEN the vehicle has a country of registration
        $countryId = CountryOfRegistrationId::GB_UK_ENG_CYM_SCO_UK_GREAT_BRITAIN;

        $stdClass = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $stdClass->countryOfRegistrationId = $countryId;

        $vehicle = new DvsaVehicle($stdClass);

        $this->vehicleServiceGetSpy->mock()
            ->willReturn($vehicle);
        /* @var UpdateVehicleContext $updateVehicleContext */
        $updateVehicleContext = XMock::of(UpdateVehicleContext::class);
        $updateVehicleContext->expects($this->once())
            ->method('getVehicle')
            ->willReturn($vehicle);

        $updateVehicleContext->expects($this->once())
            ->method('isUpdateVehicleDuringTest')
            ->willReturn(true);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('isValueChanged')
            ->with(StartTestChangeService::CHANGE_COUNTRY)
            ->willReturn(false);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('isDvlaVehicle')
            ->willReturn(false);

        $this->updateCountryRegistrationProcess->setContext($updateVehicleContext);

        // WHEN the process retrieves pre-populated data
        $data = $this->updateCountryRegistrationProcess->getPrePopulatedData();

        // THEN I can see it in the form
        $this->assertArrayHasKey('country-of-registration', $data);
        $this->assertEquals($countryId, $data['country-of-registration']);
    }

    public function testPrePopulatedData_whenDvlaVehicle_prepopulatedAsGB()
    {
        // GIVEN the vehicle has a country of registration
        $countryId = CountryOfRegistrationId::GB_UK_ENG_CYM_SCO_UK_GREAT_BRITAIN;

        $stdClass = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $stdClass->countryOfRegistrationId = $countryId;

        $vehicle = new DvsaVehicle($stdClass);

        $this->vehicleServiceGetSpy->mock()
            ->willReturn($vehicle);
        /* @var UpdateVehicleContext $updateVehicleContext */
        $updateVehicleContext = XMock::of(UpdateVehicleContext::class);

        $updateVehicleContext->expects($this->once())
            ->method('isUpdateVehicleDuringTest')
            ->willReturn(true);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('isValueChanged')
            ->with(StartTestChangeService::CHANGE_COUNTRY)
            ->willReturn(false);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('isDvlaVehicle')
            ->willReturn(true);

        $this->updateCountryRegistrationProcess->setContext($updateVehicleContext);

        // WHEN the process retrieves pre-populated data
        $data = $this->updateCountryRegistrationProcess->getPrePopulatedData();

        // THEN I can see it in the form
        $this->assertArrayHasKey('country-of-registration', $data);
        $this->assertEquals($countryId, $data['country-of-registration']);
    }

    public function testPrePopulatedData_whenDvlaVehicle_valueChangedInSession()
    {
        // GIVEN the vehicle has a country of registration
        $countryId = CountryOfRegistrationId::CY_CY_CYPRUS;

        $stdClass = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $stdClass->countryOfRegistrationId = $countryId;

        $vehicle = new DvsaVehicle($stdClass);

        $this->vehicleServiceGetSpy->mock()
            ->willReturn($vehicle);
        /* @var UpdateVehicleContext $updateVehicleContext */
        $updateVehicleContext = XMock::of(UpdateVehicleContext::class);

        $updateVehicleContext->expects($this->once())
            ->method('isUpdateVehicleDuringTest')
            ->willReturn(true);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('isValueChanged')
            ->with(StartTestChangeService::CHANGE_COUNTRY)
            ->willReturn(true);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('isDvlaVehicle')
            ->willReturn(true);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('getChangedValue')
            ->with(StartTestChangeService::CHANGE_COUNTRY)
            ->willReturn(['country' => CountryOfRegistrationId::CY_CY_CYPRUS]);

        $this->updateCountryRegistrationProcess->setContext($updateVehicleContext);

        // WHEN the process retrieves pre-populated data
        $data = $this->updateCountryRegistrationProcess->getPrePopulatedData();

        // THEN I can see it in the form
        $this->assertArrayHasKey('country-of-registration', $data);
        $this->assertEquals($countryId, $data['country-of-registration']);
    }

    public function testPrePopulatedData_whenNotDvlaVehicle_valueChangedInSession()
    {
        // GIVEN the vehicle has a country of registration
        $countryId = CountryOfRegistrationId::CY_CY_CYPRUS;

        $stdClass = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $stdClass->countryOfRegistrationId = $countryId;

        $vehicle = new DvsaVehicle($stdClass);

        $this->vehicleServiceGetSpy->mock()
            ->willReturn($vehicle);
        /* @var UpdateVehicleContext $updateVehicleContext */
        $updateVehicleContext = XMock::of(UpdateVehicleContext::class);

        $updateVehicleContext->expects($this->once())
            ->method('isUpdateVehicleDuringTest')
            ->willReturn(true);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('isValueChanged')
            ->with(StartTestChangeService::CHANGE_COUNTRY)
            ->willReturn(true);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('isDvlaVehicle')
            ->willReturn(false);

        $this->startTestChangeService
            ->expects($this->any())
            ->method('getChangedValue')
            ->with(StartTestChangeService::CHANGE_COUNTRY)
            ->willReturn(['country' => CountryOfRegistrationId::CY_CY_CYPRUS]);

        $this->updateCountryRegistrationProcess->setContext($updateVehicleContext);

        // WHEN the process retrieves pre-populated data
        $data = $this->updateCountryRegistrationProcess->getPrePopulatedData();

        // THEN I can see it in the form
        $this->assertArrayHasKey('country-of-registration', $data);
        $this->assertEquals($countryId, $data['country-of-registration']);
    }

    public function testIsAuthorised_whenVehicleUnderTestContextAndUserDoesNotHavePermissionToTestClass_shouldReturnFalse() {
        /* @var UpdateVehicleContext $updateVehicleContext */
        $updateVehicleContext = XMock::of(UpdateVehicleContext::class);

        $updateVehicleContext->expects($this->once())
            ->method('isUpdateVehicleDuringTest')
            ->willReturn(true);
        $updateVehicleContext->expects($this->once())
            ->method('getVehicle')
            ->willReturn($this->buildDvsaVehicle());
        $this->updateCountryRegistrationProcess->setContext($updateVehicleContext);
        $result = $this->updateCountryRegistrationProcess->isAuthorised($this->authorisationServiceInterface);
        $this->assertFalse($result);
    }

    private function createDvsaVehicle()
    {
        $make = new stdClass();
        $make->id = 1;
        $make->name = 'Audi';

        $model = new stdClass();
        $model->id = 4;
        $model->name = 'A4';

        $fuel = new stdClass();
        $model->code = 'PE';
        $model->name = 'Petrol';

        $colour = new stdClass();
        $colour->code = 'L';
        $colour->name = 'Grey';

        $secondaryColour = new stdClass();
        $secondaryColour->code = 'W';
        $secondaryColour->name = 'Not Stated';

        $std = new stdClass();
        $std->make = $make;
        $std->model = $model;
        $std->registration = 'reg123XSW';
        $std->vin = 'VIN98798798';
        $std->vehicleClass = null;
        $std->fuelType = $fuel;
        $std->colour = $colour;
        $std->colourSecondary = $secondaryColour;

        return new DvsaVehicle($std);
    }

    private function createUpdateCountryOfRegistrationProcess()
    {
        $this->updateCountryRegistrationProcess = new UpdateCountryOfRegistrationProcess(
            $this->countryCatalog,
            $this->url,
            $this->vehicleService,
            $this->breadcrumbsBuilder,
            $this->startTestChangeService
        );
    }

    private function getVehicle()
    {
        $vehicleStd = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $vehicleStd->id = 15;
        $emptyResource = new \stdClass();
        $emptyResource->id = null;
        $emptyResource->name = null;
        $vehicleStd->model = $emptyResource;
        $vehicleStd->make = $emptyResource;

        return new DvsaVehicle($vehicleStd);
    }

    private function buildDvsaVehicle()
    {
        $data = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();

        $data->countryOfRegistrationId = 321;

        $class = new stdClass();
        $class->code = 1;

        $data->vehicleClass = $class;

        $vehicle = new DvsaVehicle($data);

        return $vehicle;
    }
}
