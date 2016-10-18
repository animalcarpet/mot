<?php

namespace Vehicle\UpdateVehicleProperty\Process;

use Application\Service\CatalogService;
use Core\Action\AbstractRedirectActionResult;
use Core\Action\RedirectToRoute;
use Core\Routing\VehicleRouteList;
use Core\Routing\VehicleRoutes;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\InputFilter\UpdateEngineInputFilter;
use Vehicle\UpdateVehicleProperty\Form\UpdateEngineForm;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\Form\Form;
use Zend\View\Helper\Url;

class UpdateEngineProcess implements SingleStepProcessInterface, AutoWireableInterface
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

    public function __construct(
        Url $url,
        CatalogService $catalogService,
        VehicleService $vehicleService,
        VehicleTertiaryTitleBuilder $vehicleTertiaryTitleBuilder,
        VehicleEditBreadcrumbsBuilder $vehicleEditBreadcrumbsBuilder
    )
    {
        $this->url = $url;
        $this->catalogService = $catalogService;
        $this->vehicleService = $vehicleService;
        $this->vehicleEditBreadcrumbsBuilder = $vehicleEditBreadcrumbsBuilder;
        $this->vehicleTertiaryTitleBuilder = $vehicleTertiaryTitleBuilder;
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
        $request = new UpdateDvsaVehicleRequest();
        $request->setFuelTypeCode($formData[UpdateEngineForm::FIELD_FUEL_TYPE]);
        $request->setCylinderCapacity($formData[UpdateEngineForm::FIELD_CAPACITY]);
        $this->vehicleService->updateDvsaVehicle($this->context->getVehicleId(), $request);
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

        return [
            UpdateEngineForm::FIELD_FUEL_TYPE => $fuelType ? $fuelType->getCode() : null,
            UpdateEngineForm::FIELD_CAPACITY => $vehicle->getCylinderCapacity(),
        ];
    }

    /**
     * What should be displayed on the submit button control.
     *
     * @return string
     */
    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
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
        return $this->successfullEditMessage;
    }

    /**
     * The title that will be displayed on the form page
     *
     * @return string
     */
    public function getEditStepPageTitle()
    {
        return $this->editStepMessage;
    }

    /**
     * The sub title that will be displayed on the edit and review pages
     *
     * @return string
     */
    public function getPageSubTitle()
    {
        return $this->pageSubTitle;
    }

    /**
     * @param $form
     * @return UpdateVehiclePropertyViewModel Anything you want to pass to the view file
     */
    public function buildEditStepViewModel($form)
    {
        $updateVehiclePropertyViewModel = new UpdateVehiclePropertyViewModel();
        return $updateVehiclePropertyViewModel
            ->setForm($form)
            ->setSubmitButtonText($this->getSubmitButtonText())
            ->setPartial($this->templatePartial)
            ->setBackUrl(VehicleRoutes::of($this->url)->vehicleDetails($this->context->getObfuscatedVehicleId(), []))
            ->setFormActionUrl(VehicleRoutes::of($this->url)->vehicleEditEngine($this->context->getObfuscatedVehicleId()))
            ->setPageTertiaryTitle($this->vehicleTertiaryTitleBuilder->getTertiaryTitleForVehicle($this->context->getVehicle()));
    }

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartPage()
    {
        return new RedirectToRoute(VehicleRouteList::VEHICLE_DETAIL, ['id' => $this->context->getObfuscatedVehicleId()]);
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