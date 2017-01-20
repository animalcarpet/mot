<?php
namespace VehicleTest\UpdateVehicleProperty\Process;

use Application\Service\CatalogService;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommonTest\TestUtils\XMock;
use stdClass;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\UpdateColourForm;
use Vehicle\UpdateVehicleProperty\Process\UpdateColourProcess;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\View\Helper\Url;

class UpdateColourProcessTest extends \PHPUnit_Framework_TestCase
{
    const VEHICLE_ID = 12;
    const VEHICLE_VERSION = 10000;
    const VEHICLE_CLASS = VehicleClassCode::CLASS_1;
    const COLOUR_CODE = ColourCode::BEIGE;
    //const COLOUR_NAME = "Beige";
    const SECONDARY_COLOUR_CODE = ColourCode::NOT_STATED;
    //const SECONDARY_COLOUR_NAME = "Not Stated";

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
    /** @var  CatalogService | \PHPUnit_Framework_MockObject_MockObject */
    private $catalogService;

    /** @var UpdateColourProcess */
    private $sut;

    public function setUp()
    {
        $this->dvsaVehicleBuilder = new DvsaVehicleBuilder();

        $this->urlHelper = XMock::of(Url::class);
        $this->vehicleService = XMock::of(VehicleService::class);
        $this->breadcrumbsBuilder = XMock::of(VehicleEditBreadcrumbsBuilder::class);
        $this->tertiaryTitleBuilder = XMock::of(VehicleTertiaryTitleBuilder::class);
        $this->catalogService = XMock::of(CatalogService::class);

        $this->sut = new UpdateColourProcess(
            $this->urlHelper,
            $this->vehicleService,
            $this->breadcrumbsBuilder,
            $this->catalogService
        );
        $this->sut->setContext($this->buildContext());
    }

    public function testUpdateRunsVehicleService()
    {
        $formData = [
            UpdateColourForm::FIELD_COLOUR => self::COLOUR_CODE,
            UpdateColourForm::FIELD_SECONDARY_COLOUR => self::SECONDARY_COLOUR_CODE,
        ];

        $this->vehicleService->expects($this->once())
            ->method("updateDvsaVehicleAtVersion")
            ->with(
                self::VEHICLE_ID,
                self::VEHICLE_VERSION,
                (new UpdateDvsaVehicleRequest())
                ->setColourCode(self::COLOUR_CODE)
                ->setSecondaryColourCode(self::SECONDARY_COLOUR_CODE)
            );

        $this->sut->update($formData);
    }

    public function testGetPrePopulatedData()
    {
        $data = $this->sut->getPrePopulatedData();
        $this->assertSame(self::COLOUR_CODE, $data[UpdateColourForm::FIELD_COLOUR]);
        $this->assertSame(self::SECONDARY_COLOUR_CODE, $data[UpdateColourForm::FIELD_SECONDARY_COLOUR]);
    }

    public function testBuildEditStepViewModel()
    {
        $form = new UpdateColourForm([]);
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

        $colour = new stdClass();
        $colour->code = self::COLOUR_CODE;
        //$colour->name = self::COLOUR_NAME;
        $data->colour = $colour;

        $secondaryColour = new stdClass();
        $secondaryColour->code = self::SECONDARY_COLOUR_CODE;
        //$secondaryColour->name = self::SECONDARY_COLOUR_NAME;
        $data->colourSecondary = $secondaryColour;

        $vehicle = new DvsaVehicle($data);

        return $vehicle;
    }
}