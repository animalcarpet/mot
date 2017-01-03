<?php
namespace VehicleTest\UpdateVehicleProperty\Process;

use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\UpdateClassForm;
use Vehicle\UpdateVehicleProperty\Process\UpdateClassProcess;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\View\Helper\Url;

class UpdateClassProcessTest extends \PHPUnit_Framework_TestCase
{
    const VEHICLE_CLASS = VehicleClassCode::CLASS_3;
    const VEHICLE_ID = 1;

    /** @var  DvsaVehicleBuilder */
    private $dvsaVehicleBuilder;

    /** @var  Url | \PHPUnit_Framework_MockObject_MockObject */
    private $urlHelper;
    /** @var  VehicleService | \PHPUnit_Framework_MockObject_MockObject */
    private $vehicleService;
    /** @var  VehicleEditBreadcrumbsBuilder | \PHPUnit_Framework_MockObject_MockObject */
    private $breadcrumbsBuilder;
    /** @var  VehicleTertiaryTitleBuilder | \PHPUnit_Framework_MockObject_MockObject */
    private $tertiaryTitleBuilder;

    /** @var UpdateClassProcess */
    private $sut;

    public function setUp()
    {
        $this->dvsaVehicleBuilder = new DvsaVehicleBuilder();

        $this->urlHelper = XMock::of(Url::class);
        $this->vehicleService = XMock::of(VehicleService::class);
        $this->breadcrumbsBuilder = XMock::of(VehicleEditBreadcrumbsBuilder::class);
        $this->tertiaryTitleBuilder = XMock::of(VehicleTertiaryTitleBuilder::class);

        $this->sut = new UpdateClassProcess(
            $this->urlHelper,
            $this->vehicleService,
            $this->breadcrumbsBuilder,
            $this->tertiaryTitleBuilder
        );
        $this->sut->setContext($this->buildContext());
    }

    public function testUpdateRunsVehicleService()
    {
        $formData = [
            UpdateClassForm::FIELD_CLASS => self::VEHICLE_CLASS
        ];

        $this->vehicleService->expects($this->once())
            ->method("updateDvsaVehicle")
            ->with(self::VEHICLE_ID, (new UpdateDvsaVehicleRequest())->setVehicleClassCode(self::VEHICLE_CLASS));

        $this->sut->update($formData);
    }

    public function testGetPrePopulatedData()
    {
        $data = $this->sut->getPrePopulatedData();
        $this->assertSame(self::VEHICLE_CLASS, $data[UpdateClassForm::FIELD_CLASS]);
    }

    public function testBuildEditStepViewModel()
    {
        $form = new UpdateClassForm();
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
        $vehicleClassData = new \stdClass();
        $vehicleClassData->code =  self::VEHICLE_CLASS;
        $vehicleClassData->name =  self::VEHICLE_CLASS;
        
        $data->id = self::VEHICLE_ID;
        $data->vehicleClass = $vehicleClassData;

        $vehicle = new DvsaVehicle($data);

        return $vehicle;
    }
}