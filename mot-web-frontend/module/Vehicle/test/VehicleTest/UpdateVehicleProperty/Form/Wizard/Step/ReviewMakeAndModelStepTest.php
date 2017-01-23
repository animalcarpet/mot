<?php
namespace VehicleTest\UpdateVehicleProperty\Form\Step;

use Core\Action\RedirectToRoute;
use Core\FormWizard\StepResult;
use CoreTest\FormWizard\Fake\FakeStep;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Service\StartTestChangeService;
use stdClass;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Context;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\ReviewMakeAndModelStep;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\UpdateMakeStep;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\UpdateModelStep;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Zend\View\Helper\Url;

class ReviewMakeAndModelStepTest extends \PHPUnit_Framework_TestCase
{
    private $url;
    private $vehicleService;
    private $vehicleEditBreadcrumbsBuilder;
    private $formUuid;

    /** @var  StartTestChangeService */
    private $startTestChangeService;

    protected function setUp()
    {
        $this->url = XMock::of(Url::class);
        $this->vehicleService = XMock::of(VehicleService::class);
        $this->vehicleEditBreadcrumbsBuilder = XMock::of(VehicleEditBreadcrumbsBuilder::class);
        $this->startTestChangeService = XMock::of(StartTestChangeService::class);
        $this->vehicleEditBreadcrumbsBuilder->expects($this->any())->method("getVehicleEditBreadcrumbs")->willReturn(["vehicle", "change"]);
        $this->formUuid = uniqid();
    }

    /**
     * @dataProvider stepStatuses
     * @param $isMakeStepValid
     * @param $isModelStepValid
     * @param $expectedResponse
     */
    public function testIsValid($isMakeStepValid, $isModelStepValid, $expectedResponse)
    {
        $makeStep = $this->createStep("make Step", $isMakeStepValid);
        $modelStep = $this->createStep("model Step", $isModelStepValid);
        $reviewStep = $this->createReviewStep();

        $makeStep->setNextStep($modelStep);

        $modelStep
            ->setPrevStep($makeStep)
            ->setNextStep($reviewStep);

        $reviewStep->setPrevStep($modelStep);

        $this->assertEquals($expectedResponse, $reviewStep->isValid($this->formUuid));
    }

    public function stepStatuses()
    {
        return [
            [true, false, false],
            [false, true, false],
            [false, false, false],
            [true, true, true],
        ];
    }

    public function testExecuteGetReturnsStepResult()
    {
        $reviewStep = $this->createReviewStepWithMakeAndModelSteps();
        $result = $reviewStep->executeGet($this->formUuid);

        $this->assertInstanceOf(StepResult::class, $result);
    }

    public function testExecutePostReturnsRoute()
    {
        $reviewStep = $this->createReviewStepWithMakeAndModelSteps();
        $result = $reviewStep->executePost([], $this->formUuid);

        $this->assertInstanceOf(RedirectToRoute::class, $result);
    }

    public function testExecutePost_changeUnderTest_returnsRoute()
    {
        $this->startTestChangeService
            ->expects($this->at(0))
            ->method('getChangedValue')
            ->with(StartTestChangeService::URL)
            ->willReturn([
                'url' => 'start-test-confirmation'
            ]);

        $this->startTestChangeService
            ->expects($this->at(1))
            ->method('getChangedValue')
            ->with(StartTestChangeService::NO_REGISTRATION)
            ->willReturn([
                'noRegistration' => '0'
            ]);

        $this->startTestChangeService
            ->expects($this->at(2))
            ->method('getChangedValue')
            ->with(StartTestChangeService::SOURCE)
            ->willReturn([
                'source' => '1'
            ]);
        $reviewStep = $this->createReviewStepWithMakeAndModelSteps('change-under-test');

        $result = $reviewStep->executePost([], $this->formUuid);

        $this->assertInstanceOf(RedirectToRoute::class, $result);
        $this->assertSame(
            'start-test-confirmation',
            $reviewStep->getRoute()->getRouteName()
        );
    }

    private function createReviewStep()
    {
        return new ReviewMakeAndModelStep(
            $this->url,
            $this->vehicleEditBreadcrumbsBuilder,
            $this->vehicleService,
            $this->startTestChangeService
        );
    }

    private function createReviewStepWithMakeAndModelSteps($route = 'change')
    {
        $dvsaVehicle = $this->createDvsaVehicle();
        $context = new UpdateVehicleContext($dvsaVehicle, "1w", $route);

        $makeStep = $this->createStep(UpdateMakeStep::NAME, true);
        $modelStep = $this->createStep(UpdateModelStep::NAME, true);
        $reviewStep = $this->createReviewStep();

        $makeStep->setNextStep($modelStep);

        $modelStep
            ->setPrevStep($makeStep)
            ->setNextStep($reviewStep);

        $reviewStep
            ->setPrevStep($modelStep)
            ->setContext($context)
        ;

        return $reviewStep;
    }

    private function createStep($name, $isValid)
    {
        return new FakeStep($name, $isValid);
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
        $std->id = 283;
        $std->version = 10000;
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
