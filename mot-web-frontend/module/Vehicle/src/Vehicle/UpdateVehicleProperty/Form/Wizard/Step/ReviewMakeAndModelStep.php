<?php

namespace Vehicle\UpdateVehicleProperty\Form\Wizard\Step;

use Core\Action\RedirectToRoute;
use Core\FormWizard\AbstractStep as AbstractWizardStep;
use Core\FormWizard\LayoutData;
use Core\FormWizard\StepResult;
use Core\FormWizard\WizardContextInterface;
use Core\Routing\VehicleRouteList;
use Core\Routing\VehicleRoutes;
use Core\ViewModel\Gds\Table\GdsTable;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\TypeCheck;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaMotTest\Service\StartTestChangeService;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\MakeForm;
use Vehicle\UpdateVehicleProperty\Form\ModelForm;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Context;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\ReviewVehiclePropertyViewModel;
use Zend\View\Helper\Url;

class ReviewMakeAndModelStep extends AbstractWizardStep implements AutoWireableInterface
{
    const NAME = 'review-make-and-model';

    private $url;
    private $breadcrumbsBuilder;
    private $tertiaryTitleBuilder;
    private $vehicleService;
    private $formUuid;
    /** @var StartTestChangeService */
    private $startTestChangeService;

    /**
     * ReviewMakeAndModelStep constructor.
     *
     * @param Url                           $url
     * @param VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder
     * @param VehicleService                $vehicleService
     * @param StartTestChangeService        $startTestChangeService
     */
    public function __construct(
        Url $url,
        VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder,
        VehicleService $vehicleService,
        StartTestChangeService $startTestChangeService
    ) {
        parent::__construct();

        $this->url = $url;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->vehicleService = $vehicleService;
        $this->tertiaryTitleBuilder = new VehicleTertiaryTitleBuilder();
        $this->startTestChangeService = $startTestChangeService;
    }

    /**
     * @var UpdateVehicleContext
     */
    protected $context;

    public function getName()
    {
        return self::NAME;
    }

    public function setContext(WizardContextInterface $context)
    {
        TypeCheck::assertInstance($context, UpdateVehicleContext::class);

        return parent::setContext($context);
    }

    public function executeGet($formUuid = null)
    {
        $this->formUuid = $formUuid;

        return $this->buildResult();
    }

    public function executePost(array $formData, $formUuid = null)
    {
        $this->formUuid = $formUuid;

        try {
            $this->saveData();

            return $this->getNextRoute();
        } catch (ValidationException $exception) {
            $errors = $exception->getDisplayMessages();
        }

        return $this->buildResult($errors);
    }

    private function buildResult(array $errors = [])
    {
        $table = $this->transformFormDataIntoGdsTable();
        $layoutData = $this->getLayoutData();
        $viewModel = $this->createViewModel($table, $this->formUuid);

        return new StepResult($layoutData, $viewModel, $errors, 'vehicle/update-vehicle-property/review');
    }

    private function transformFormDataIntoGdsTable()
    {
        $table = new GdsTable();

        $makeStep = $this->getPrevStepWithName(UpdateMakeStep::NAME);
        $changeMakeUrl = $makeStep->getRoute(['formUuid' => $this->formUuid])->toString($this->url);

        $table->newRow()->setLabel('Make')->setValue($this->getMake())->addActionLink('Change', $changeMakeUrl);

        $modelStep = $this->getPrevStepWithName(UpdateModelStep::NAME);
        $changeModelUrl = $modelStep->getRoute(['formUuid' => $this->formUuid])->toString($this->url);
        $table->newRow()->setLabel('Model')->setValue($this->getModel())->addActionLink('Change', $changeModelUrl);

        return $table;
    }

    private function getMake()
    {
        $formData = $this->getMakeStepStoredData();
        if ($this->hasOtherMake()) {
            return $formData[MakeForm::FIELD_OTHER_MAKE_NAME];
        }

        return $formData['name'];
    }

    private function getMakeId()
    {
        $formData = $this->getMakeStepStoredData();

        return $formData[MakeForm::FIELD_MAKE_NAME];
    }

    private function hasOtherMake()
    {
        $formData = $this->getMakeStepStoredData();

        return $formData[MakeForm::FIELD_MAKE_NAME] === MakeForm::OTHER_ID;
    }

    private function getMakeStepStoredData()
    {
        return $this->getStoredData($this->formUuid)[UpdateMakeStep::NAME];
    }

    private function getModel()
    {
        $formData = $this->getModelStepStoredData();
        if ($this->hasOtherModel()) {
            return $formData[ModelForm::FIELD_OTHER_MODEL_NAME];
        }

        return $formData['name'];
    }

    private function getModelId()
    {
        $formData = $this->getModelStepStoredData();

        return $formData[ModelForm::FIELD_MODEL_NAME];
    }

    private function hasOtherModel()
    {
        $formData = $this->getModelStepStoredData();
        if ($this->hasOtherMake() || $formData[ModelForm::FIELD_MODEL_NAME] === ModelForm::OTHER_ID) {
            return true;
        }

        return false;
    }

    private function getModelStepStoredData()
    {
        return $this->getStoredData($this->formUuid)[UpdateModelStep::NAME];
    }

    protected function getLayoutData()
    {
        $breadcrumbs = $this
            ->breadcrumbsBuilder
            ->getVehicleEditBreadcrumbs('Review changes', $this->context->getObfuscatedVehicleId());

        $layoutData = new LayoutData();
        $layoutData->setBreadcrumbs($breadcrumbs);
        $layoutData->setPageSubTitle('Vehicle');
        $layoutData->setPageTitle('Review make and model changes');

        return $layoutData;
    }

    protected function createViewModel(GdsTable $table, $formUuid)
    {
        $formActionUrl = $this->getRoute(['formUuid' => $formUuid])->toString($this->url);
        $tertiaryTitle = $this->tertiaryTitleBuilder->getTertiaryTitleForVehicle($this->context->getVehicle());

        return (new ReviewVehiclePropertyViewModel())
            ->setSubmitButtonText('Save changes')
            ->setFormActionUrl($formActionUrl)
            ->setPageTertiaryTitle($tertiaryTitle)
            ->setSummary($table)
            ->setCancelLinkLabel('Cancel and return to vehicle')
            ->setCancelUrl(VehicleRoutes::of($this->url)->vehicleDetails($this->context->getObfuscatedVehicleId()));
    }

    public function isValid($formUuid)
    {
        $step = $this;
        while ($step->hasPrevStep()) {
            $step = $step->getPrevStep();
            if ($step->isValid($formUuid) === false) {
                return false;
            }
        }

        return true;
    }

    public function getStoredData($formUuid)
    {
        return $this->formContainer->get($formUuid, $this->getSessionStoreKey());
    }

    public function getRoute(array $queryParams = [])
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            $route = new RedirectToRoute(
                $this->startTestChangeService->getChangedValue(StartTestChangeService::URL)['url'],
                [
                    'id' => $this->context->getObfuscatedVehicleId(),
                    'noRegistration' => $this->startTestChangeService->getChangedValue(StartTestChangeService::NO_REGISTRATION)['noRegistration'],
                    'source' => $this->startTestChangeService->getChangedValue(StartTestChangeService::SOURCE)['source'],
                    'property' => self::NAME,
                ]
            );
            $route->addSuccessMessage('Vehicle make and model has been successfully changed');

            return $route;
        }

        return new RedirectToRoute(
            VehicleRouteList::VEHICLE_CHANGE_MAKE_AND_MODEL,
            ['id' => $this->context->getObfuscatedVehicleId(), 'property' => self::NAME],
            $queryParams
        );
    }

    private function saveData()
    {
        $updateRequest = new UpdateDvsaVehicleRequest();
        if ($this->hasOtherMake()) {
            $updateRequest->setMakeOther($this->getMake());
        } else {
            $updateRequest->setMakeId($this->getMakeId());
        }

        if ($this->hasOtherModel()) {
            $updateRequest->setModelOther($this->getModel());
        } else {
            $updateRequest->setModelId($this->getModelId());
        }

        $this->vehicleService->updateDvsaVehicleAtVersion(
            $this->context->getVehicle()->getId(),
            $this->context->getVehicle()->getVersion(),
            $updateRequest
        );
    }

    private function getNextRoute()
    {
        $route = new RedirectToRoute(VehicleRouteList::VEHICLE_DETAIL, ['id' => $this->context->getObfuscatedVehicleId()]);
        $route->addSuccessMessage('Vehicle make and model has been successfully changed.');

        return $route;
    }

    /**
     * @return \Core\ViewModel\Header\HeaderTertiaryList|string
     */
    private function getTertiaryTitleForVehicle()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return '';
        }

        return $this->tertiaryTitleBuilder->getTertiaryTitleForVehicle($this->context->getVehicle());
    }
}
