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
use Vehicle\UpdateVehicleProperty\Form\UpdateColourForm;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\Form\Form;
use Zend\View\Helper\Url;

class UpdateColourProcess implements UpdateVehicleInterface, AutoWireableInterface
{
    const PAGE_TITLE = "Change colour";
    const PAGE_TITLE_UPDATE_DURING_TEST = "What is the vehicle's colour?";

    private $urlHelper;
    private $vehicleService;
    private $breadcrumbsBuilder;
    private $tertiaryTitleBuilder;
    /** @var  UpdateVehicleContext */
    private $context;
    private $catalogService;
    private $colours;
    /** @var  StartTestChangeService */
    private $startTestChangeService;

    public function __construct(
        Url $urlHelper,
        VehicleService $vehicleService,
        VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder,
        CatalogService $catalogService,
        StartTestChangeService $startTestChangeService
    ) {
        $this->urlHelper = $urlHelper;
        $this->vehicleService = $vehicleService;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->tertiaryTitleBuilder = new VehicleTertiaryTitleBuilder();
        $this->catalogService = $catalogService;
        $this->startTestChangeService = $startTestChangeService;
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
        if ($this->context->isUpdateVehicleDuringTest()) {
            $this->startTestChangeService->saveChange(StartTestChangeService::CHANGE_COLOUR, [
                StartTestChangeService::PRIMARY_COLOUR => $formData[UpdateColourForm::FIELD_COLOUR],
                StartTestChangeService::SECONDARY_COLOUR => $formData[UpdateColourForm::FIELD_SECONDARY_COLOUR]
            ]);
            $this->startTestChangeService->updateChangedValueStatus(StartTestChangeService::CHANGE_COLOUR, true);
        } else {
            $request = new UpdateDvsaVehicleRequest();
            $request->setColourCode($formData[UpdateColourForm::FIELD_COLOUR]);
            $request->setSecondaryColourCode($formData[UpdateColourForm::FIELD_SECONDARY_COLOUR]);

            $this->vehicleService->updateDvsaVehicle(
                $this->context->getVehicleId(),
                $request
            );
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
        $isValueChanged = $this->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_COLOUR);
        $changedValue = $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_COLOUR);

        return [
            UpdateColourForm::FIELD_COLOUR => $isValueChanged
                ? $changedValue[StartTestChangeService::PRIMARY_COLOUR]
                : $vehicle->getColour()->getCode(),
            UpdateColourForm::FIELD_SECONDARY_COLOUR => $isValueChanged
                ? $changedValue[StartTestChangeService::SECONDARY_COLOUR]
                : $vehicle->getColourSecondary()->getCode(),
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

        return self::PAGE_TITLE;
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
            return $this->breadcrumbsBuilder->getChangeVehicleUnderTestBreadcrumbs($this->context->getObfuscatedVehicleId());
        }

        return $this->breadcrumbsBuilder->getVehicleEditBreadcrumbs(
            self::PAGE_TITLE,
            $this->context->getObfuscatedVehicleId());
    }

    /**
     * Zend form used to edit values
     *
     * @return Form
     */
    public function createEmptyForm()
    {
        return new UpdateColourForm($this->getColours());
    }

    /**
     * Tells what message should be shown to the user when the form has been successfully submitted
     *
     * @return string
     */
    public function getSuccessfulEditMessage()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return "Vehicle colour has been successfully changed";
        }

        return "Colour has been successfully changed";
    }

    /**
     * The title that will be displayed on the form page
     *
     * @return string
     */
    public function getEditStepPageTitle()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return self::PAGE_TITLE_UPDATE_DURING_TEST;
        }

        return self::PAGE_TITLE;
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

        return "Vehicle";
    }

    /**
     * @param $form
     * @return Object Anything you want to pass to the view file
     */
    public function buildEditStepViewModel($form)
    {
        $changeUnderTestRoute = $this->context->isUpdateVehicleDuringTest();
        $veBackUrl = $this->startTestChangeService->vehicleExaminerReturnUrl($this->context->getObfuscatedVehicleId());
        $veActionUrl = VehicleRoutes::of($this->urlHelper)->changeColour($this->context->getObfuscatedVehicleId());
        $underTestBackUrl = $this->startTestChangeService->underTestReturnUrl($this->context->getObfuscatedVehicleId());
        $underTestActionUrl = VehicleRoutes::of($this->urlHelper)->changeUnderTestColour($this->context->getObfuscatedVehicleId());

        return (new UpdateVehiclePropertyViewModel())
            ->setForm($form)
            ->setPageTertiaryTitle($changeUnderTestRoute ? '' : $this->tertiaryTitleBuilder->getTertiaryTitleForVehicle($this->context->getVehicle()))
            ->setSubmitButtonText($this->getSubmitButtonText())
            ->setBackLinkText($this->getBackButtonText())
            ->setPartial('/vehicle/update-vehicle-property/partials/edit-colour')
            ->setBackUrl($changeUnderTestRoute ? $underTestBackUrl : $veBackUrl)
            ->setFormActionUrl($changeUnderTestRoute ? $underTestActionUrl : $veActionUrl);
    }

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartPage()
    {
        return new RedirectToRoute(
            VehicleRouteList::VEHICLE_DETAIL,
            ['id' => $this->context->getObfuscatedVehicleId()]
        );
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
        return '';
    }

    private function getColours()
    {
        if($this->colours == null) {
            $this->colours = $this->catalogService->getColours();
        }

        return $this->colours;
    }
}