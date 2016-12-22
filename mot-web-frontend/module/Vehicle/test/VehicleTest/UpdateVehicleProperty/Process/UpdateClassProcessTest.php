<?php
namespace VehicleTest\UpdateVehicleProperty\Process;

use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\ApiClient\test\Resource\Item\DvlaVehicleTest;
use DvsaCommonTest\Builder\DvlaVehicleBuilder;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Service\StartTestChangeService;
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
    const SESSION_VEHICLE_CLASS = VehicleClassCode::CLASS_1;
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

    /** @var  StartTestChangeService */
    private $startTestChangeService;

    /** @var  DvlaVehicleBuilder */
    private $dvlaVehicleBuilder;

    public function setUp()
    {
        $this->dvsaVehicleBuilder = new DvsaVehicleBuilder();
        $this->dvlaVehicleBuilder = new DvlaVehicleBuilder();

        $this->urlHelper = XMock::of(Url::class);
        $this->vehicleService = XMock::of(VehicleService::class);
        $this->breadcrumbsBuilder = XMock::of(VehicleEditBreadcrumbsBuilder::class);
        $this->tertiaryTitleBuilder = XMock::of(VehicleTertiaryTitleBuilder::class);
        $this->startTestChangeService = XMock::of(StartTestChangeService::class);

        $this->sut = new UpdateClassProcess(
            $this->urlHelper,
            $this->vehicleService,
            $this->breadcrumbsBuilder,
            $this->tertiaryTitleBuilder,
            $this->startTestChangeService
        );
    }

    public function testUpdateRunsVehicleService()
    {
        $formData = [
            UpdateClassForm::FIELD_CLASS => self::VEHICLE_CLASS
        ];

        $this->sut->setContext($this->buildContext($this->buildDvsaVehicle(), 'change'));

        $this->vehicleService->expects($this->once())
            ->method("updateDvsaVehicle")
            ->with(self::VEHICLE_ID, (new UpdateDvsaVehicleRequest())->setVehicleClassCode(self::VEHICLE_CLASS));

        $this->sut->update($formData);
    }

    public function testGetPrePopulatedData()
    {
        $this->sut->setContext($this->buildContext($this->buildDvsaVehicle(), 'change'));
        $this->startTestChangeService
            ->expects($this->once())
            ->method('isValueChanged')
            ->with(StartTestChangeService::CHANGE_CLASS)
            ->willReturn(false);
        $this->startTestChangeService
            ->expects($this->never())
            ->method('getChangedValue')
            ->with(StartTestChangeService::CHANGE_CLASS)
            ->willReturn('1');
        $data = $this->sut->getPrePopulatedData();

        $this->assertSame(self::VEHICLE_CLASS, $data[UpdateClassForm::FIELD_CLASS]);
    }

    public function testGetPrePopulatedData_dvlaVehicle_shouldHaveNullField()
    {
        $this->sut->setContext($this->buildContext($this->buildDvlaVehicle(), 'change'));

        $data = $this->sut->getPrePopulatedData();

        $this->assertSame(null, $data[UpdateClassForm::FIELD_CLASS]);
    }

    public function testGetPrePopulatedDataFromChangeSession_dvlaVehicle()
    {
        $this->sut->setContext($this->buildContext($this->buildDvlaVehicle(), 'change'));
        $this->startTestChangeService
            ->expects($this->once())
            ->method('isValueChanged')
            ->with(StartTestChangeService::CHANGE_CLASS)
            ->willReturn(true);
        $this->startTestChangeService
            ->expects($this->once())
            ->method('getChangedValue')
            ->with(StartTestChangeService::CHANGE_CLASS)
            ->willReturn('1');
        $data = $this->sut->getPrePopulatedData();

        $this->assertSame(self::SESSION_VEHICLE_CLASS, $data[UpdateClassForm::FIELD_CLASS]);
    }

    public function testGetPrePopulatedDataFromChangeSession()
    {
        $this->sut->setContext($this->buildContext($this->buildDvsaVehicle(), 'change-under-test'));
        $this->startTestChangeService
            ->expects($this->once())
            ->method('isValueChanged')
            ->with(StartTestChangeService::CHANGE_CLASS)
            ->willReturn(true);
        $this->startTestChangeService
            ->expects($this->once())
            ->method('getChangedValue')
            ->with(StartTestChangeService::CHANGE_CLASS)
            ->willReturn('1');
        $data = $this->sut->getPrePopulatedData();

        $this->assertSame(self::SESSION_VEHICLE_CLASS, $data[UpdateClassForm::FIELD_CLASS]);
    }

    public function testBuildEditStepViewModel()
    {
        $form = new UpdateClassForm();
        $this->sut->setContext($this->buildContext($this->buildDvsaVehicle(), 'change'));
        $viewModel = $this->sut->buildEditStepViewModel($form);

        $this->assertInstanceOf(UpdateVehiclePropertyViewModel::class, $viewModel);
    }

    public function testBuildEditStepViewModel_whileVehicleUnderTestContext_shouldHaveCorrectLabels()
    {
        $form = new UpdateClassForm();
        $this->sut->setContext($this->buildContext($this->buildDvsaVehicle(), 'change-under-test'));
        /** @var UpdateVehiclePropertyViewModel $viewModel */
        $viewModel = $this->sut->buildEditStepViewModel($form);

        $this->assertInstanceOf(UpdateVehiclePropertyViewModel::class, $viewModel);
        $this->assertSame('Back', $viewModel->getBackLinkText());
        $this->assertSame('Continue', $viewModel->getSubmitButtonText());
    }

    private function buildContext($vehicle, $routeContext)
    {
        return new UpdateVehicleContext($vehicle, "abc", $routeContext);
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

    private function buildDvlaVehicle()
    {
        $data = $this->dvlaVehicleBuilder->getEmptyVehicleStdClass();


        $data->id = self::VEHICLE_ID;

        $vehicle = new DvlaVehicle($data);

        return $vehicle;
    }
}