<?php
namespace VehicleTest\UpdateVehicleProperty\Form\Step;

use Core\Action\RedirectToRoute;
use Core\FormWizard\StepResult;
use CoreTest\FormWizard\Fake\FakeStep;
use DvsaCommon\Dto\Vehicle\MakeDto;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\ApiClient\Vehicle\Dictionary\MakeApiResource;
use stdClass;
use Vehicle\UpdateVehicleProperty\Form\MakeForm;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\UpdateMakeStep;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Context;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Zend\View\Helper\Url;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;

class UpdateMakeStepTest extends \PHPUnit_Framework_TestCase
{
    private $url;
    private $makeApiResource;
    private $vehicleEditBreadcrumbsBuilder;

    protected function setUp()
    {
        $this->url = XMock::of(Url::class);
        $this->makeApiResource = XMock::of(MakeApiResource::class);
        $this->vehicleEditBreadcrumbsBuilder = XMock::of(VehicleEditBreadcrumbsBuilder::class);

        $this->makeApiResource->expects($this->any())->method("getList")->willReturn($this->getMakeList());
        $this->vehicleEditBreadcrumbsBuilder->expects($this->any())->method("getVehicleEditBreadcrumbs")->willReturn(["vehicle", "change"]);
    }

    public function testGetMethodReturnsStepResult()
    {
        $context = new Context($this->createDvsaVehicle(), "1w");
        $step = new UpdateMakeStep($this->url, $this->makeApiResource, $this->vehicleEditBreadcrumbsBuilder);
        $step->setContext($context);

        $result = $step->executeGet();

        $this->assertInstanceOf(StepResult::class, $result);
    }

    public function testPrepopulatesFormWithDefaultValuesWhenDataAreNotStoredInContainer()
    {
        $dvsaVehicle = $this->createDvsaVehicle();
        $context = new Context($dvsaVehicle, "1w");
        $step = new UpdateMakeStep($this->url, $this->makeApiResource, $this->vehicleEditBreadcrumbsBuilder);
        $step->setContext($context);

        $result = $step->executeGet("form-uuid-674574");

        /** @var MakeForm $form */
        $form = $result->getViewModel()->getForm();
        $makeId = $form->getMakeElement()->getValue();

        $this->assertEquals($makeId, $dvsaVehicle->getMake()->getId());
    }

    public function testExecutePostReturnsStepResultIfDataAreInvalid()
    {
        $dvsaVehicle = $this->createDvsaVehicle();
        $context = new Context($dvsaVehicle, "1w");
        $step = new UpdateMakeStep($this->url, $this->makeApiResource, $this->vehicleEditBreadcrumbsBuilder);
        $step->setContext($context);

        $result = $step->executePost([],"form-uuid-674574");
        $this->assertInstanceOf(StepResult::class, $result);
    }

    public function testExecutePostRedirectsToNextStepIfDataAreValid()
    {
        $dvsaVehicle = $this->createDvsaVehicle();
        $context = new Context($dvsaVehicle, "1w");

        $modelStep = new FakeStep("model step", true);
        $modelStep->setContext($context);

        $step = new UpdateMakeStep($this->url, $this->makeApiResource, $this->vehicleEditBreadcrumbsBuilder);
        $step->setContext($context);
        $step->setNextStep($modelStep);

        $result = $step->executePost([MakeForm::FIELD_MAKE_NAME => $dvsaVehicle->getMake()->getId()],"form-uuid-674574");
        $this->assertInstanceOf(RedirectToRoute::class, $result);
    }

    private function getMakeList()
    {
        return [
            (new MakeDto())->setId(1)->setName("Audi"),
            (new MakeDto())->setId(2)->setName("BMW"),
            (new MakeDto())->setId(3)->setName("Citroen"),
            (new MakeDto())->setId(4)->setName("Daimler"),
            (new MakeDto())->setId(MakeForm::OTHER_ID)->setName(MakeForm::OTHER_NAME),
        ];
    }

    private function createDvsaVehicle()
    {
        $make = new stdClass();
        $make->id = 1;
        $make->name = "Audi";

        $model = new stdClass();
        $model->id = 2;
        $model->name = "A4";

        $fuel = new stdClass();
        $model->code = "PE";
        $model->name = "Petrol";

        $colour = new stdClass();
        $colour->code = "L";
        $colour->name = "Grey";

        $secondaryColour = new stdClass();
        $secondaryColour->code = "W";
        $secondaryColour->name = "Not Stated";

        $std = new stdClass();
        $std->make = $make;
        $std->model = $model;
        $std->registration = "reg123XSW";
        $std->vin = "VIN98798798";
        $std->vehicleClass = null;
        $std->fuelType = $fuel;
        $std->colour = $colour;
        $std->colourSecondary = $secondaryColour;

        return new DvsaVehicle($std);
    }
}
