<?php
namespace VehicleTest\UpdateVehicleProperty\Process;

use Application\Service\CatalogService;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Model\FuelType;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Service\StartTestChangeService;
use stdClass;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\UpdateEngineForm;
use Vehicle\UpdateVehicleProperty\Process\UpdateEngineProcess;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\View\Helper\Url;

class UpdateEngineProcessTest extends \PHPUnit_Framework_TestCase
{
    const VEHICLE_ID = 1;
    const VEHICLE_VERSION = 10000;
    const VEHICLE_CAPACITY = '1223';
    const SESSION_VEHICLE_CAPACITY = '1400';
    const FUEL_TYPE = FuelTypeCode::DIESEL;
    const SESSION_FUEL_TYPE = FuelTypeCode::PETROL;

    /** @var  DvsaVehicleBuilder */
    private $dvsaVehicleBuilder;

    /** @var  CatalogService | \PHPUnit_Framework_MockObject_MockObject */
    protected $catalogService;
    /** @var  Url | \PHPUnit_Framework_MockObject_MockObject */
    private $urlHelper;
    /** @var  VehicleService | \PHPUnit_Framework_MockObject_MockObject */
    private $vehicleService;
    /** @var  VehicleEditBreadcrumbsBuilder | \PHPUnit_Framework_MockObject_MockObject */
    private $breadcrumbsBuilder;
    /** @var  VehicleTertiaryTitleBuilder | \PHPUnit_Framework_MockObject_MockObject */
    private $tertiaryTitleBuilder;

    /** @var UpdateEngineProcess */
    private $sut;
    /** @var  StartTestChangeService */
    private $startTestChangeService;

    public function setUp()
    {
        $this->dvsaVehicleBuilder = new DvsaVehicleBuilder();

        $this->urlHelper = XMock::of(Url::class);
        $this->vehicleService = XMock::of(VehicleService::class);
        $this->breadcrumbsBuilder = XMock::of(VehicleEditBreadcrumbsBuilder::class);
        $this->tertiaryTitleBuilder = XMock::of(VehicleTertiaryTitleBuilder::class);
        $this->catalogService = XMock::of(CatalogService::class);
        $this->startTestChangeService = XMock::of(StartTestChangeService::class);

        $this->sut = new UpdateEngineProcess(
            $this->urlHelper,
            $this->catalogService,
            $this->vehicleService,
            $this->tertiaryTitleBuilder,
            $this->breadcrumbsBuilder,
            $this->startTestChangeService
        );
    }

    public function testUpdateRunsVehicleService()
    {
        $formData = [
            UpdateEngineForm::FIELD_CAPACITY => self::VEHICLE_CAPACITY,
            UpdateEngineForm::FIELD_FUEL_TYPE => self::FUEL_TYPE,
        ];
        $this->sut->setContext($this->buildContext('change'));

        $this->vehicleService->expects($this->once())
            ->method("updateDvsaVehicleAtVersion")
            ->with(
                self::VEHICLE_ID,
                self::VEHICLE_VERSION,
                (new UpdateDvsaVehicleRequest())->setFuelTypeCode(self::FUEL_TYPE)
                    ->setCylinderCapacity(self::VEHICLE_CAPACITY)
            );

        $this->sut->update($formData);
    }

    public function testGetPrePopulatedData()
    {
        $this->sut->setContext($this->buildContext('change'));
        $this->startTestChangeService
            ->expects($this->once())
            ->method('isValueChanged')
            ->with(StartTestChangeService::CHANGE_ENGINE)
            ->willReturn(false);
        $this->startTestChangeService
            ->expects($this->once())
            ->method('getChangedValue')
            ->with(StartTestChangeService::CHANGE_ENGINE)
            ->willReturn([
                'fuelType' => 'PE',
                'cylinderCapacity' => '1400'
            ]);
        $data = $this->sut->getPrePopulatedData();
        $this->assertSame(self::VEHICLE_CAPACITY, $data[UpdateEngineForm::FIELD_CAPACITY]);
        $this->assertSame(self::FUEL_TYPE, $data[UpdateEngineForm::FIELD_FUEL_TYPE]);
    }

    public function testGetPrePopulatedDataFromSession()
    {
        $this->sut->setContext($this->buildContext('change-under-test'));
        $this->startTestChangeService
            ->expects($this->once())
            ->method('isValueChanged')
            ->with(StartTestChangeService::CHANGE_ENGINE)
            ->willReturn(true);
        $this->startTestChangeService
            ->expects($this->once())
            ->method('getChangedValue')
            ->with(StartTestChangeService::CHANGE_ENGINE)
            ->willReturn([
                'fuelType' => 'PE',
                'cylinderCapacity' => '1400'
            ]);
        $data = $this->sut->getPrePopulatedData();
        $this->assertSame(self::SESSION_VEHICLE_CAPACITY, $data[UpdateEngineForm::FIELD_CAPACITY]);
        $this->assertSame(self::SESSION_FUEL_TYPE, $data[UpdateEngineForm::FIELD_FUEL_TYPE]);
    }

    public function testBuildEditStepViewModel()
    {
        $types = FuelType::getOrderedFuelTypeList();
        $this->sut->setContext($this->buildContext('change'));
        $form = new UpdateEngineForm(array_combine($types, array_values($types)));
        $viewModel = $this->sut->buildEditStepViewModel($form);
        $this->assertInstanceOf(UpdateVehiclePropertyViewModel::class, $viewModel);
    }

    public function testBuildEditStepViewModel_whileVehicleUnderTestContext_shouldHaveCorrectLabels()
    {
        $types = FuelType::getOrderedFuelTypeList();
        $form = new UpdateEngineForm(array_combine($types, array_values($types)));
        $this->sut->setContext($this->buildContext('change-under-test'));
        /** @var UpdateVehiclePropertyViewModel $viewModel */
        $viewModel = $this->sut->buildEditStepViewModel($form);

        $this->assertInstanceOf(UpdateVehiclePropertyViewModel::class, $viewModel);
        $this->assertSame('Back', $viewModel->getBackLinkText());
        $this->assertSame('Continue', $viewModel->getSubmitButtonText());
    }


    private function buildContext($routeContext)
    {
        return new UpdateVehicleContext($this->buildDvsaVehicle(), "abc", $routeContext);
    }

    private function buildDvsaVehicle()
    {
        $data = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $data->id = self::VEHICLE_ID;
        $fuelType = new stdClass();
        $fuelType->code = self::FUEL_TYPE;
        $data->fuelType = $fuelType;
        $data->cylinderCapacity = self::VEHICLE_CAPACITY;
        $data->vehicleClass = new stdClass();

        $vehicle = new DvsaVehicle($data);

        return $vehicle;
    }
}