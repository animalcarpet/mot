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
    const VEHICLE_CAPACITY = '1223';
    const FUEL_TYPE = FuelTypeCode::DIESEL;

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

    public function setUp()
    {
        $this->dvsaVehicleBuilder = new DvsaVehicleBuilder();

        $this->urlHelper = XMock::of(Url::class);
        $this->vehicleService = XMock::of(VehicleService::class);
        $this->breadcrumbsBuilder = XMock::of(VehicleEditBreadcrumbsBuilder::class);
        $this->tertiaryTitleBuilder = XMock::of(VehicleTertiaryTitleBuilder::class);
        $this->catalogService = XMock::of(CatalogService::class);

        $this->sut = new UpdateEngineProcess(
            $this->urlHelper,
            $this->catalogService,
            $this->vehicleService,
            $this->tertiaryTitleBuilder,
            $this->breadcrumbsBuilder
        );
        $this->sut->setContext($this->buildContext());
    }

    public function testUpdateRunsVehicleService()
    {
        $formData = [
            UpdateEngineForm::FIELD_CAPACITY => self::VEHICLE_CAPACITY,
            UpdateEngineForm::FIELD_FUEL_TYPE => self::FUEL_TYPE,
        ];

        $this->vehicleService->expects($this->once())
            ->method("updateDvsaVehicle")
            ->with(self::VEHICLE_ID, (new UpdateDvsaVehicleRequest())
                ->setFuelTypeCode(self::FUEL_TYPE)
                ->setCylinderCapacity(self::VEHICLE_CAPACITY)
            );

        $this->sut->update($formData);
    }

    public function testGetPrePopulatedDate()
    {
        $data = $this->sut->getPrePopulatedData();
        $this->assertSame(self::VEHICLE_CAPACITY, $data[UpdateEngineForm::FIELD_CAPACITY]);
        $this->assertSame(self::FUEL_TYPE, $data[UpdateEngineForm::FIELD_FUEL_TYPE]);
    }

    public function testBuildEditStepViewModel()
    {
        $types = FuelType::getOrderedFuelTypeList();
        $form = new UpdateEngineForm(array_combine($types, array_values($types)));
        $viewModel = $this->sut->buildEditStepViewModel($form);
        $this->assertInstanceOf(UpdateVehiclePropertyViewModel::class, $viewModel);

    }

    private function buildContext()
    {
        return new UpdateVehicleContext($this->buildDvsaVehicle(), "abc");
    }

    private function buildDvsaVehicle()
    {
        $data = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $data->id = self::VEHICLE_ID;
        $data->fuelTypeCode = new stdClass();
        $data->fuelTypeCode->code = self::FUEL_TYPE;
        $data->cylinderCapacity = self::VEHICLE_CAPACITY;

        $vehicle = new DvsaVehicle($data);

        return $vehicle;
    }
}