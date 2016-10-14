<?php

namespace VehicleTest\UpdateVehicleProperty;

use Core\Catalog\CountryOfRegistration\CountryOfRegistrationCatalog;
use CoreTest\Service\StubCatalogService;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Process\UpdateCountryOfRegistrationProcess;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Zend\View\Helper\Url;

class UpdateCountryOfRegistrationProcessTest extends \PHPUnit_Framework_TestCase
{
    /** @var  DvsaVehicleBuilder */
    private $dvsaVehicleBuilder;

    /** @var UpdateCountryOfRegistrationProcess */
    private $process;

    /** @var MethodSpy */
    private $vehicleServiceUpdateSpy;

    /** @var MethodSpy */
    private $vehicleServiceGetSpy;

    /** @var DvsaVehicle */
    private $vehicle;

    private $obfuscatedId = "OBF-15-SCATED";

    public function setUp()
    {
        $this->dvsaVehicleBuilder = new DvsaVehicleBuilder();

        $vehicleService = XMock::of(VehicleService::class);
        $this->vehicleServiceUpdateSpy = new MethodSpy($vehicleService, 'updateDvsaVehicle');
        $this->vehicleServiceGetSpy = new MethodSpy($vehicleService, 'getDvsaVehicleById');

        $this->process = new UpdateCountryOfRegistrationProcess(
            new CountryOfRegistrationCatalog(new StubCatalogService()),
            XMock::of(Url::class),
            $vehicleService,
            XMock::of(VehicleEditBreadcrumbsBuilder::class)
        );

        $vehicleStd = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $vehicleStd->id = 15;
        $emptyResource = new \stdClass();
        $emptyResource->id = null;
        $emptyResource->name = null;
        $vehicleStd->model = $emptyResource;
        $vehicleStd->make = $emptyResource;

        $this->vehicle = new DvsaVehicle($vehicleStd);

        $context = new UpdateVehicleContext($this->vehicle, $this->obfuscatedId);

        $this->process->setContext($context);
    }

    public function testUpdate()
    {
        // WHEN the process updates vehicle's country of registration
        $countryId = 12;
        $this->process->update(['country-of-registration' => 12]);

        // THEN vehicle api service is being called
        $this->assertEquals(1, $this->vehicleServiceUpdateSpy->invocationCount());

        // for a correct vehicle
        $this->assertSame($this->vehicle->getId(), $this->vehicleServiceUpdateSpy->paramsForLastInvocation()[0]);

        // with correct country
        /** @var UpdateDvsaVehicleRequest $updateRequest */
        $updateRequest = $this->vehicleServiceUpdateSpy->paramsForLastInvocation()[1];
        $this->assertEquals($countryId , $updateRequest->getCountryOfRegistrationId());
    }

    public function testPrePopulatedData()
    {
        // GIVEN the vehicle has a country of registration
        $countryId = 18;

        $stdClass = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $stdClass->countryOfRegistrationId = $countryId;

        $vehicle = new DvsaVehicle($stdClass);

        $this->vehicleServiceGetSpy->mock()
            ->willReturn($vehicle);

        // WHEN the process retrieves pre-populated data
        $data = $this->process->getPrePopulatedData();

        // THEN I can see it in the form
        $this->assertArrayHasKey('country-of-registration', $data);
        $this->assertEquals($countryId, $data['country-of-registration']);
    }
}
