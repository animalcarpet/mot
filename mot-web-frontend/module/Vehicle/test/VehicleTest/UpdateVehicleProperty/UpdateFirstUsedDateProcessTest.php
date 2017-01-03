<?php

namespace VehicleTest\UpdateVehicleProperty;

use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\FirstUsedDateForm;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Vehicle\UpdateVehicleProperty\Process\UpdateFirstUsedDateProcess;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Zend\View\Helper\Url;

class UpdateFirstUsedDateProcessTest extends \PHPUnit_Framework_TestCase
{
    /** @var  DvsaVehicleBuilder */
    private $dvsaVehicleBuilder;

    /** @var UpdateFirstUsedDateProcess */
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

        $this->process = new UpdateFirstUsedDateProcess(
            XMock::of(Url::class),
            $vehicleService,
            XMock::of(VehicleEditBreadcrumbsBuilder::class)
        );

        $vehicleStd = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $vehicleStd->id = 15;
        $vehicleStd->vehicleClass = new \stdClass();

        $this->vehicle = new DvsaVehicle($vehicleStd);

        $context = new UpdateVehicleContext($this->vehicle, $this->obfuscatedId);

        $this->process->setContext($context);
    }

    public function testUpdate()
    {
        // WHEN the process updates vehicle's first used date
        $date = new \DateTime("1-12-2001");

        $this->process->update($this->getFormData($date));

        // THEN vehicle api service is being called
        $this->assertEquals(1, $this->vehicleServiceUpdateSpy->invocationCount());

        // for a correct vehicle
        $this->assertSame($this->vehicle->getId(), $this->vehicleServiceUpdateSpy->paramsForLastInvocation()[0]);

        // with correct first used date
        /** @var UpdateDvsaVehicleRequest $updateRequest */
        $updateRequest = $this->vehicleServiceUpdateSpy->paramsForLastInvocation()[1];
        $this->assertEquals($date, $updateRequest->getFirstUsedDate());
        $this->assertEquals($date->format('Y-m-d'), $updateRequest->getFirstUsedDate()->format('Y-m-d'));
    }

    public function testPrePopulatedData()
    {
        // GIVEN the vehicle has a first used date
        $date = new \DateTime("02-10-2010");

        $stdClass = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $stdClass->firstUsedDate = $date->format('Y-m-d');
        $stdClass->vehicleClass = new \stdClass();

        $vehicle = new DvsaVehicle($stdClass);

        $this->vehicleServiceGetSpy->mock()
            ->willReturn($vehicle);

        // WHEN the process retrieves pre-populated data
        $data = $this->process->getPrePopulatedData();

        // THEN I can see it in the form
        $this->assertArrayHasKey(FirstUsedDateForm::FIELD_DATE_DAY, $data);
        $this->assertArrayHasKey(FirstUsedDateForm::FIELD_DATE_MONTH, $data);
        $this->assertArrayHasKey(FirstUsedDateForm::FIELD_DATE_YEAR, $data);
        $this->assertEquals($this->getFormData($date), $data);
    }

    private function getFormData(\DateTime $date)
    {
        return [
            FirstUsedDateForm::FIELD_DATE_DAY => $date->format('d'),
            FirstUsedDateForm::FIELD_DATE_MONTH => $date->format('m'),
            FirstUsedDateForm::FIELD_DATE_YEAR => $date->format('Y')
        ];
    }
}
