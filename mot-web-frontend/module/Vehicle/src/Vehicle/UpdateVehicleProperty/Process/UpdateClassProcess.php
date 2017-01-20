<?php
namespace Vehicle\UpdateVehicleProperty\Process;

use Core\Action\AbstractRedirectActionResult;
use Core\Action\RedirectToRoute;
use Core\Routing\VehicleRouteList;
use Core\Routing\VehicleRoutes;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\UpdateClassForm;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\Form\Form;
use Zend\View\Helper\Url;

class UpdateClassProcess implements SingleStepProcessInterface, AutoWireableInterface
{
    const PAGE_TITLE = "Change MOT test class";

    /** @var UpdateVehicleContext */
    private $context;
    private $urlHelper;
    private $vehicleService;
    private $breadcrumbsBuilder;
    private $tertiaryTitleBuilder;

    public function __construct(
        Url $urlHelper,
        VehicleService $vehicleService,
        VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder,
        VehicleTertiaryTitleBuilder $tertiaryTitleBuilder
    ) {
        $this->urlHelper = $urlHelper;
        $this->vehicleService = $vehicleService;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->tertiaryTitleBuilder = $tertiaryTitleBuilder;
    }

    public function setContext(FormContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * Will make a call to API to update the data from the form
     *
     * @param $formData
     */
    public function update($formData)
    {
        $request = new UpdateDvsaVehicleRequest();
        $vehicleClassCode = $formData[UpdateClassForm::FIELD_CLASS];
        $request->setVehicleClassCode($vehicleClassCode);

        $this->vehicleService->updateDvsaVehicleAtVersion(
            $this->context->getVehicleId(),
            $this->context->getVehicle()->getVersion(),
            $request
        );
    }

    /**
     * Gets the values that the form should be pre-populated with.
     * (e.g. old values)
     * @return array
     */
    public function getPrePopulatedData()
    {
        $vehicle = $this->getVehicle();

        return [UpdateClassForm::FIELD_CLASS => $vehicle->getVehicleClass()->getCode()];
    }

    /**
     * What should be displayed on the submit button control.
     *
     * @return string
     */
    public function getSubmitButtonText()
    {
        return self::PAGE_TITLE;
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
        return $this->breadcrumbsBuilder->getVehicleEditBreadcrumbs(
            self::PAGE_TITLE,
            $this->context->getObfuscatedVehicleId());
    }

    /**
     * Zend form used to edit values
     *
     * @return UpdateClassForm
     */
    public function createEmptyForm()
    {
        return new UpdateClassForm();
    }

    /**
     * Tells what message should be shown to the user when the form has been successfully submitted
     *
     * @return string
     */
    public function getSuccessfulEditMessage()
    {
        return "MOT test class has been successfully changed.";
    }

    /**
     * The title that will be displayed on the form page
     *
     * @return string
     */
    public function getEditStepPageTitle()
    {
        return self::PAGE_TITLE;
    }

    /**
     * The sub title that will be displayed on the edit and review pages
     *
     * @return string
     */
    public function getPageSubTitle()
    {
        return "Vehicle";
    }

    /**
     * @param $form
     * @return Object Anything you want to pass to the view file
     */
    public function buildEditStepViewModel($form)
    {
        return (new UpdateVehiclePropertyViewModel())
            ->setForm($form)
            ->setPageTertiaryTitle($this->tertiaryTitleBuilder->getTertiaryTitleForVehicle($this->getVehicle()))
            ->setSubmitButtonText($this->getSubmitButtonText())
            ->setPartial('/vehicle/update-vehicle-property/partials/edit-class')
            ->setBackUrl(VehicleRoutes::of($this->urlHelper)->vehicleDetails($this->context->getObfuscatedVehicleId()))
            ->setFormActionUrl(VehicleRoutes::of($this->urlHelper)->changeClass($this->context->getObfuscatedVehicleId()));
    }

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartPage()
    {
        return new RedirectToRoute(VehicleRouteList::VEHICLE_DETAIL,
            ['id' => $this->context->getObfuscatedVehicleId()]);
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
        return '';
    }

    /**
     * @return DvsaVehicle
     */
    private function getVehicle()
    {
        return $this->context->getVehicle();
    }
}