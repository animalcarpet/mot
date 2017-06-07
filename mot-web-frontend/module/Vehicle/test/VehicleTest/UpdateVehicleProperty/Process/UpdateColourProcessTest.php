<?php

namespace VehicleTest\UpdateVehicleProperty\Process;

use Application\Service\CatalogService;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Service\StartTestChangeService;
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
    const SESSION_COLOUR_CODE = ColourCode::PURPLE;
    const SECONDARY_COLOUR_CODE = ColourCode::NOT_STATED;
    const SESSION_SECONDARY_COLOUR_CODE = ColourCode::BEIGE;

    /** @var DvsaVehicleBuilder */
    private $dvsaVehicleBuilder;

    /** @var Url | \PHPUnit_Framework_MockObject_MockObject */
    private $urlHelper;
    /** @var VehicleService | \PHPUnit_Framework_MockObject_MockObject */
    private $vehicleService;
    /** @var VehicleEditBreadcrumbsBuilder | \PHPUnit_Framework_MockObject_MockObject */
    private $breadcrumbsBuilder;
    /** @var VehicleTertiaryTitleBuilder | \PHPUnit_Framework_MockObject_MockObject */
    private $tertiaryTitleBuilder;
    /** @var CatalogService | \PHPUnit_Framework_MockObject_MockObject */
    private $catalogService;
    /** @var  MotAuthorisationServiceInterface */
    private $authorisationServiceInterface;

    /** @var UpdateColourProcess */
    private $sut;

    /** @var StartTestChangeService */
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
        $this->authorisationServiceInterface = XMock::of(MotAuthorisationServiceInterface::class);

        $this->sut = new UpdateColourProcess(
            $this->urlHelper,
            $this->vehicleService,
            $this->breadcrumbsBuilder,
            $this->catalogService,
            $this->startTestChangeService
        );
    }

    public function testUpdateRunsVehicleService()
    {
        $formData = [
            UpdateColourForm::FIELD_COLOUR => self::COLOUR_CODE,
            UpdateColourForm::FIELD_SECONDARY_COLOUR => self::SECONDARY_COLOUR_CODE,
        ];
        $this->sut->setContext($this->buildContext('change'));

        $this->vehicleService->expects($this->once())
            ->method('updateDvsaVehicleAtVersion')
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
        $this->sut->setContext($this->buildContext('change'));
        $this->startTestChangeService
            ->expects($this->once())
            ->method('isValueChanged')
            ->with(StartTestChangeService::CHANGE_COLOUR)
            ->willReturn(false);
        $this->startTestChangeService
            ->expects($this->once())
            ->method('getChangedValue')
            ->with(StartTestChangeService::CHANGE_COLOUR)
            ->willReturn([
                'primaryColour' => 'K',
                'secondaryColour' => 'S',
            ]);
        $data = $this->sut->getPrePopulatedData();
        $this->assertSame(self::COLOUR_CODE, $data[UpdateColourForm::FIELD_COLOUR]);
        $this->assertSame(self::SECONDARY_COLOUR_CODE, $data[UpdateColourForm::FIELD_SECONDARY_COLOUR]);
    }

    public function testGetPrePopulatedDataFromSession()
    {
        $this->sut->setContext($this->buildContext('change-under-test'));
        $this->startTestChangeService
            ->expects($this->once())
            ->method('isValueChanged')
            ->with(StartTestChangeService::CHANGE_COLOUR)
            ->willReturn(true);
        $this->startTestChangeService
            ->expects($this->once())
            ->method('getChangedValue')
            ->with(StartTestChangeService::CHANGE_COLOUR)
            ->willReturn([
                'primaryColour' => 'K',
                'secondaryColour' => 'S',
            ]);
        $data = $this->sut->getPrePopulatedData();
        $this->assertSame(self::SESSION_COLOUR_CODE, $data[UpdateColourForm::FIELD_COLOUR]);
        $this->assertSame(self::SESSION_SECONDARY_COLOUR_CODE, $data[UpdateColourForm::FIELD_SECONDARY_COLOUR]);
    }

    public function testBuildEditStepViewModel()
    {
        $form = new UpdateColourForm([]);
        $this->sut->setContext($this->buildContext('change'));
        $viewModel = $this->sut->buildEditStepViewModel($form);
        $this->assertInstanceOf(UpdateVehiclePropertyViewModel::class, $viewModel);
    }

    public function testBuildEditStepViewModel_whileVehicleUnderTestContext_shouldHaveCorrectLabels()
    {
        $form = new UpdateColourForm([]);
        $this->sut->setContext($this->buildContext('change-under-test'));
        /** @var UpdateVehiclePropertyViewModel $viewModel */
        $viewModel = $this->sut->buildEditStepViewModel($form);

        $this->assertInstanceOf(UpdateVehiclePropertyViewModel::class, $viewModel);
        $this->assertSame('Back', $viewModel->getBackLinkText());
        $this->assertSame('Continue', $viewModel->getSubmitButtonText());
    }

    public function testIsAuthorised_whenVehicleUnderTestContextAndUserDoesNotHavePermissionToTestClass_shouldReturnFalse() {
        $this->sut->setContext($this->buildContext('change-under-test'));
        $result = $this->sut->isAuthorised($this->authorisationServiceInterface);
        $this->assertFalse($result);
    }

    private function buildContext($routeContext)
    {
        return new UpdateVehicleContext($this->buildDvsaVehicle(), 'abc', $routeContext);
    }

    private function buildDvsaVehicle()
    {
        $data = $this->dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $data->id = self::VEHICLE_ID;

        $colour = new stdClass();
        $colour->code = self::COLOUR_CODE;

        $class = new stdClass();
        $class->code = 1;
        $class->name = 1;

        //$colour->name = self::COLOUR_NAME;
        $data->colour = $colour;

        $data->vehicleClass = $class;

        $secondaryColour = new stdClass();
        $secondaryColour->code = self::SECONDARY_COLOUR_CODE;
        //$secondaryColour->name = self::SECONDARY_COLOUR_NAME;
        $data->colourSecondary = $secondaryColour;

        $vehicle = new DvsaVehicle($data);

        return $vehicle;
    }
}
