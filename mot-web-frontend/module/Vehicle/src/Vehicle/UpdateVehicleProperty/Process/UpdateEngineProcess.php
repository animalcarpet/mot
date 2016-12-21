<?php

namespace Vehicle\UpdateVehicleProperty\Process;

use Application\Service\CatalogService;
use Core\Action\AbstractRedirectActionResult;
use Core\Action\RedirectToRoute;
use Core\Routing\MotTestRoutes;
use Core\Routing\VehicleRouteList;
use Core\Routing\VehicleRoutes;
use Core\TwoStepForm\FormContextInterface;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaMotTest\Service\StartTestChangeService;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\InputFilter\UpdateEngineInputFilter;
use Vehicle\UpdateVehicleProperty\Form\UpdateEngineForm;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\Form\Form;
use Zend\View\Helper\Url;

class UpdateEngineProcess implements UpdateVehicleInterface, AutoWireableInterface
{
    protected $pageSubTitle = 'Vehicle';
    protected $templatePartial = 'partials/edit-engine';
    protected $editStepMessage = 'Change engine specification';
    protected $submitButtonText = 'Change engine specification';
    protected $successfullEditMessage = 'The engine specification has been changed successfully';

    /** @var  FormContextInterface| UpdateVehicleContext */
    private $context;
    private $url;
    private $catalogService;
    private $vehicleService;
    private $vehicleEditBreadcrumbsBuilder;
    private $vehicleTertiaryTitleBuilder;
    /** @var  StartTestChangeService */
    private $startTestChangeService;

    public function __construct(
        Url $url,
        CatalogService $catalogService,
        VehicleService $vehicleService,
        VehicleTertiaryTitleBuilder $vehicleTertiaryTitleBuilder,
        VehicleEditBreadcrumbsBuilder $vehicleEditBreadcrumbsBuilder,
        StartTestChangeService $startTestChangeService
    )
    {
        $this->url = $url;
        $this->catalogService = $catalogService;
        $this->vehicleService = $vehicleService;
        $this->vehicleEditBreadcrumbsBuilder = $vehicleEditBreadcrumbsBuilder;
        $this->vehicleTertiaryTitleBuilder = $vehicleTertiaryTitleBuilder;
        $this->startTestChangeService = $startTestChangeService;
    }

    public function setContext(FormContextInterface $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Will make a call to API to update the data from the form
     *
     * @param $formData
     */
    public function update($formData)
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            $this->startTestChangeService->saveChange(StartTestChangeService::CHANGE_ENGINE, [
                StartTestChangeService::FUEL_TYPE => $formData[UpdateEngineForm::FIELD_FUEL_TYPE],
                StartTestChangeService::CYLINDER_CAPACITY => $formData[UpdateEngineForm::FIELD_CAPACITY]
            ]);
            $this->startTestChangeService->updateChangedValueStatus(StartTestChangeService::CHANGE_ENGINE, true);
        } else {
            $request = new UpdateDvsaVehicleRequest();
            $request->setFuelTypeCode($formData[UpdateEngineForm::FIELD_FUEL_TYPE]);
            $request->setCylinderCapacity($formData[UpdateEngineForm::FIELD_CAPACITY]);
            $this->vehicleService->updateDvsaVehicle($this->context->getVehicleId(), $request);
        }
    }

    /**
     * Gets the values that the form should be pre-populated with.
     * (e.g. old values)
     * @return array
     */
    public function getPrePopulatedData()
    {
        $vehicle = $this->context->getVehicle();
        $fuelType = $vehicle->getFuelType();
        $isValueChanged = $this->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_ENGINE);
        $changedValue = $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_ENGINE);

        return [
            UpdateEngineForm::FIELD_FUEL_TYPE => $isValueChanged
                ? $changedValue[StartTestChangeService::FUEL_TYPE]
                : $fuelType->getCode(),
            UpdateEngineForm::FIELD_CAPACITY => $isValueChanged
                ? $changedValue[StartTestChangeService::CYLINDER_CAPACITY]
                : $vehicle->getCylinderCapacity(),
        ];
    }

    /**
     * What should be displayed on the submit button control.
     *
     * @return string
     */
    public function getSubmitButtonText()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return "Continue";
        }

        return $this->submitButtonText;
    }

    /**
     * What should be displayed on the submit button control.
     *
     * @return string
     */
    public function getBackButtonText()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return "Back";
        }

        return "Cancel and return to vehicle";
    }

    /**
     * Creates breadcrumbs for edit page.
     * Returning null means there are no breadcrumbs to display.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @return array
     */
    public function getBreadcrumbs(MotAuthorisationServiceInterface $authorisationService)
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return $this->vehicleEditBreadcrumbsBuilder->getChangeVehicleUnderTestBreadcrumbs($this->context->getObfuscatedVehicleId());
        }

        return $this->vehicleEditBreadcrumbsBuilder->getVehicleEditBreadcrumbs(
            $this->getEditStepPageTitle(),
            $this->context->getObfuscatedVehicleId()
        );
    }

    /**
     * Zend form used to edit values
     *
     * @return Form
     */
    public function createEmptyForm()
    {
        $fuelTypes = $this->catalogService->getFuelTypes();
        $updateEngineInputFilter = new UpdateEngineInputFilter($fuelTypes);
        $updateEngineForm = new UpdateEngineForm($fuelTypes);

        return $updateEngineForm
            ->setEngineCapacityValidator($updateEngineInputFilter->getEngineCapacityValidator())
            ->setInputFilter($updateEngineInputFilter->getInputFilter());
    }

    /**
     * Tells what message should be shown to the user when the form has been successfully submitted
     *
     * @return string
     */
    public function getSuccessfulEditMessage()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return "Vehicle engine specification has been successfully changed";
        }

        return $this->successfullEditMessage;
    }

    /**
     * The title that will be displayed on the form page
     *
     * @return string
     */
    public function getEditStepPageTitle()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return 'Engine and fuel type';
        }

        return $this->editStepMessage;
    }

    /**
     * The sub title that will be displayed on the edit and review pages
     *
     * @return string
     */
    public function getPageSubTitle()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return self::PAGE_SUBTITLE_UPDATE_DURING_TEST;
        }

        return $this->pageSubTitle;
    }

    /**
     * @param $form
     * @return UpdateVehiclePropertyViewModel Anything you want to pass to the view file
     */
    public function buildEditStepViewModel($form)
    {
        $changeUnderTestRoute = $this->context->isUpdateVehicleDuringTest();
        $veBackUrl = $this->startTestChangeService->vehicleExaminerReturnUrl($this->context->getObfuscatedVehicleId());
        $veActionUrl = VehicleRoutes::of($this->url)->vehicleEditEngine($this->context->getObfuscatedVehicleId());
        $underTestBackUrl = $this->startTestChangeService->underTestReturnUrl($this->context->getObfuscatedVehicleId());
        $underTestActionUrl = VehicleRoutes::of($this->url)->changeUnderTestEngine($this->context->getObfuscatedVehicleId());

        $updateVehiclePropertyViewModel = new UpdateVehiclePropertyViewModel();
        return $updateVehiclePropertyViewModel
            ->setForm($form)
            ->setSubmitButtonText($this->getSubmitButtonText())
            ->setBackLinkText($this->getBackButtonText())
            ->setPartial($this->templatePartial)
            ->setBackUrl($changeUnderTestRoute ? $underTestBackUrl : $veBackUrl)
            ->setFormActionUrl($changeUnderTestRoute ? $underTestActionUrl : $veActionUrl)
            ->setPageTertiaryTitle($changeUnderTestRoute ? '' : $this->vehicleTertiaryTitleBuilder->getTertiaryTitleForVehicle($this->context->getVehicle()));
    }

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartPage()
    {
        return new RedirectToRoute(VehicleRouteList::VEHICLE_DETAIL, ['id' => $this->context->getObfuscatedVehicleId()]);
    }

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartUnderTestPage()
    {
        return new RedirectToRoute($this->startTestChangeService->getChangedValue(StartTestChangeService::URL)['url'],
            [
                'id' => $this->context->getObfuscatedVehicleId(),
                'noRegistration' => $this->startTestChangeService->getChangedValue(StartTestChangeService::NO_REGISTRATION)['noRegistration'],
                'source' => $this->startTestChangeService->getChangedValue(StartTestChangeService::SOURCE)['source']
            ]);
    }

    /**
     * Says if the users is authorised to reach the page
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     * @return bool
     */
    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService)
    {
        return $authorisationService->isGranted(PermissionInSystem::VEHICLE_UPDATE);
    }

    public function getEditPageLede()
    {
        return null;
    }
}