<?php

namespace VehicleTest\UpdateVehicleProperty\Form\Step;

use Core\Action\RedirectToRoute;
use Core\FormWizard\StepResult;
use CoreTest\FormWizard\Fake\FakeStep;
use DvsaCommon\Dto\Vehicle\MakeDto;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\ApiClient\Vehicle\Dictionary\MakeApiResource;
use DvsaMotTest\Service\StartTestChangeService;
use stdClass;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\MakeForm;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\UpdateMakeStep;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Zend\View\Helper\Url;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;

class UpdateMakeStepTest extends \PHPUnit_Framework_TestCase
{
    private $url;
    private $makeApiResource;
    private $vehicleEditBreadcrumbsBuilder;

    /** @var StartTestChangeService */
    private $startTestChangeService;

    protected function setUp()
    {
        $this->url = XMock::of(Url::class);
        $this->makeApiResource = XMock::of(MakeApiResource::class);
        $this->vehicleEditBreadcrumbsBuilder = XMock::of(VehicleEditBreadcrumbsBuilder::class);
        $this->startTestChangeService = XMock::of(StartTestChangeService::class);
        $this->makeApiResource->expects($this->any())->method('getList')->willReturn($this->getMakeList());
        $this->vehicleEditBreadcrumbsBuilder->expects($this->any())->method('getVehicleEditBreadcrumbs')->willReturn(['vehicle', 'change']);
    }

    public function testGetMethodReturnsStepResult()
    {
        $context = new UpdateVehicleContext($this->createDvsaVehicle(), '1w', 'change');
        $step = new UpdateMakeStep(
            $this->url,
            $this->makeApiResource,
            $this->vehicleEditBreadcrumbsBuilder,
            $this->startTestChangeService
        );
        $step->setContext($context);

        $result = $step->executeGet();

        $this->assertInstanceOf(StepResult::class, $result);
    }

    public function testPrepopulatesFormWithDefaultValuesWhenDataAreNotStoredInContainer()
    {
        $dvsaVehicle = $this->createDvsaVehicle();
        $context = new UpdateVehicleContext($dvsaVehicle, '1w', 'change');
        $step = new UpdateMakeStep(
            $this->url,
            $this->makeApiResource,
            $this->vehicleEditBreadcrumbsBuilder,
            $this->startTestChangeService
        );
        $step->setContext($context);

        $result = $step->executeGet('form-uuid-674574');

        /** @var MakeForm $form */
        $form = $result->getViewModel()->getForm();
        $makeId = $form->getMakeElement()->getValue();

        $this->assertEquals($makeId, $dvsaVehicle->getMake()->getId());
    }

    public function testPrePopulateChangeUnderTest_FormWithDefaultValuesWhenUserHasNotUpdatedBothMakeAndModel()
    {
        $dvsaVehicle = $this->createDvsaVehicle();
        $context = new UpdateVehicleContext($dvsaVehicle, '1w', 'change-under-test');
        $step = new UpdateMakeStep(
            $this->url,
            $this->makeApiResource,
            $this->vehicleEditBreadcrumbsBuilder,
            $this->startTestChangeService
        );
        $step->setContext($context);

        $this->vehicleEditBreadcrumbsBuilder
            ->expects($this->any())
            ->method('getChangeVehicleUnderTestBreadcrumbs')
            ->willReturn(['vehicle', 'change']);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('isAuthorisedToTestClass')
            ->willReturn(true);

        $result = $step->executeGet("form-uuid-674574");

        /** @var MakeForm $form */
        $form = $result->getViewModel()->getForm();
        $makeId = $form->getMakeElement()->getValue();

        $this->assertEquals($makeId, $dvsaVehicle->getMake()->getId());
    }

    public function testPrePopulateChangeUnderTest_FormWithChangedValuesWhenUserHasUpdatedBothMakeAndModel()
    {
        $dvsaVehicle = $this->createDvsaVehicle();
        $context = new UpdateVehicleContext($dvsaVehicle, '1w', 'change-under-test');
        $step = new UpdateMakeStep(
            $this->url,
            $this->makeApiResource,
            $this->vehicleEditBreadcrumbsBuilder,
            $this->startTestChangeService
        );
        $step->setContext($context);

        $this->vehicleEditBreadcrumbsBuilder
            ->expects($this->any())
            ->method('getChangeVehicleUnderTestBreadcrumbs')
            ->willReturn(['vehicle', 'change']);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('isMakeAndModelChanged')
            ->willReturn(true);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('getChangedValue')
            ->willReturn(['makeId' => 3]);

        $this->startTestChangeService
            ->expects($this->once())
            ->method('isAuthorisedToTestClass')
            ->willReturn(true);

        $result = $step->executeGet("form-uuid-674574");

        /** @var MakeForm $form */
        $form = $result->getViewModel()->getForm();
        $makeId = $form->getMakeElement()->getValue();

        $this->assertEquals($makeId, 3);
    }

    public function testExecutePostReturnsStepResultIfDataAreInvalid()
    {
        $dvsaVehicle = $this->createDvsaVehicle();
        $context = new UpdateVehicleContext($dvsaVehicle, '1w', 'change');
        $step = new UpdateMakeStep(
            $this->url,
            $this->makeApiResource,
            $this->vehicleEditBreadcrumbsBuilder,
            $this->startTestChangeService
        );
        $step->setContext($context);

        $result = $step->executePost([], 'form-uuid-674574');
        $this->assertInstanceOf(StepResult::class, $result);
    }

    public function testExecutePostRedirectsToNextStepIfDataAreValid()
    {
        $dvsaVehicle = $this->createDvsaVehicle();
        $context = new UpdateVehicleContext($dvsaVehicle, '1w', 'change');

        $modelStep = new FakeStep('model step', true);
        $modelStep->setContext($context);

        $step = new UpdateMakeStep(
            $this->url,
            $this->makeApiResource,
            $this->vehicleEditBreadcrumbsBuilder,
            $this->startTestChangeService
        );
        $step->setContext($context);
        $step->setNextStep($modelStep);

        $result = $step->executePost([MakeForm::FIELD_MAKE_NAME => $dvsaVehicle->getMake()->getId()], 'form-uuid-674574');
        $this->assertInstanceOf(RedirectToRoute::class, $result);
    }

    public function testChangeUnderTest_validPost_redirectedToChangeUnderTestModelStep()
    {
        $dvsaVehicle = $this->createDvsaVehicle();
        $context = new UpdateVehicleContext($dvsaVehicle, '1w', 'change-under-test');

        $modelStep = new FakeStep('model step', true);
        $modelStep->setContext($context);

        $step = new UpdateMakeStep(
            $this->url,
            $this->makeApiResource,
            $this->vehicleEditBreadcrumbsBuilder,
            $this->startTestChangeService
        );
        $step->setContext($context);
        $step->setNextStep($modelStep);
        $routeName = $step->getRoute();

        $result = $step->executePost([MakeForm::FIELD_MAKE_NAME => $dvsaVehicle->getMake()->getId()], 'form-uuid-674574');
        $this->assertInstanceOf(RedirectToRoute::class, $result);
        $this->assertSame('make', $routeName->getRouteParams()['property']);
        $this->assertSame('vehicle/detail/change-under-test/make-and-model', $routeName->getRouteName());
    }

    private function getMakeList()
    {
        return [
            (new MakeDto())->setId(1)->setName('Audi'),
            (new MakeDto())->setId(2)->setName('BMW'),
            (new MakeDto())->setId(3)->setName('Citroen'),
            (new MakeDto())->setId(4)->setName('Daimler'),
            (new MakeDto())->setId(MakeForm::OTHER_ID)->setName(MakeForm::OTHER_NAME),
        ];
    }

    private function createDvsaVehicle()
    {
        $make = new stdClass();
        $make->id = 1;
        $make->name = 'Audi';

        $model = new stdClass();
        $model->id = 2;
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

        $class = new stdClass();
        $class->code = 1;
        $class->name =1;

        $weightSource = new stdClass();
        $weightSource->code = "U";
        $weightSource->name = "unladen";

        $std = new stdClass();
        $std->make = $make;
        $std->model = $model;
        $std->registration = "reg123XSW";
        $std->vin = "VIN98798798";
        $std->vehicleClass = $class;
        $std->fuelType = $fuel;
        $std->colour = $colour;
        $std->colourSecondary = $secondaryColour;
        $std->weightSource = $weightSource;

        return new DvsaVehicle($std);
    }
}
