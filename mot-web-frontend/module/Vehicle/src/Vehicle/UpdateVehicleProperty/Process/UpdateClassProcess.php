<?php
namespace Vehicle\UpdateVehicleProperty\Process;

use Core\Action\AbstractRedirectActionResult;
use Core\Action\RedirectToRoute;
use Core\Routing\VehicleRouteList;
use Core\Routing\VehicleRoutes;
use Core\TwoStepForm\FormContextInterface;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaMotTest\Service\StartTestChangeService;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\UpdateClassForm;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\View\Helper\Url;

class UpdateClassProcess implements UpdateVehicleInterface , AutoWireableInterface
{
    const PAGE_TITLE = "Change MOT test class";
    const PAGE_TITLE_UPDATE_DURING_TEST = "What is the vehicle's test class?";

    /** @var UpdateVehicleContext */
    private $context;
    private $urlHelper;
    private $vehicleService;
    private $breadcrumbsBuilder;
    private $tertiaryTitleBuilder;
    /** @var  StartTestChangeService */
    private $startTestChangeService;

    public function __construct(
        Url $urlHelper,
        VehicleService $vehicleService,
        VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder,
        VehicleTertiaryTitleBuilder $tertiaryTitleBuilder,
        StartTestChangeService $startTestChangeService
    ) {
        $this->urlHelper = $urlHelper;
        $this->vehicleService = $vehicleService;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->tertiaryTitleBuilder = $tertiaryTitleBuilder;
        $this->startTestChangeService = $startTestChangeService;
    }

    public function setContext(FormContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @param array $formData
     */
    public function update($formData)
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            $this->startTestChangeService->saveChange(StartTestChangeService::CHANGE_CLASS, $formData[UpdateClassForm::FIELD_CLASS]);
            $this->startTestChangeService->updateChangedValueStatus(StartTestChangeService::CHANGE_CLASS, true);
        } else {
            $request = new UpdateDvsaVehicleRequest();
            $vehicleClassCode = $formData[UpdateClassForm::FIELD_CLASS];
            $request->setVehicleClassCode($vehicleClassCode);

            $this->vehicleService->updateDvsaVehicleAtVersion(
                $this->context->getVehicleId(),
                $this->context->getVehicle()->getVersion(),
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
        $vehicle = $this->getVehicle();
        $isValueChanged = $this->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_CLASS);

        if ($vehicle instanceof DvsaVehicle) {
            return [UpdateClassForm::FIELD_CLASS => $this->getDvsaVehicleClass($isValueChanged, $vehicle)];
        }

        if ($vehicle instanceof DvlaVehicle) {
            return [UpdateClassForm::FIELD_CLASS => $this->getDvlaVehicleClass($isValueChanged)];
        }
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
        if ($this->context->isUpdateVehicleDuringTest()) {
            return "Vehicle test class has been successfully changed";
        }

        return "MOT test class has been successfully changed.";
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
        if ( $this->context->isUpdateVehicleDuringTest()) {
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
        $veActionUrl = VehicleRoutes::of($this->urlHelper)->changeClass($this->context->getObfuscatedVehicleId());
        $underTestBackUrl = $this->startTestChangeService->underTestReturnUrl($this->context->getObfuscatedVehicleId());
        $underTestActionUrl = VehicleRoutes::of($this->urlHelper)->changeUnderTestClass($this->context->getObfuscatedVehicleId());

        return (new UpdateVehiclePropertyViewModel())
            ->setForm($form)
            ->setPageTertiaryTitle($this->getTertiaryTitleForVehicle())
            ->setSubmitButtonText($this->getSubmitButtonText())
            ->setBackLinkText($this->getBackButtonText())
            ->setPartial('/vehicle/update-vehicle-property/partials/edit-class')
            ->setBackUrl($changeUnderTestRoute ? $underTestBackUrl : $veBackUrl)
            ->setFormActionUrl($changeUnderTestRoute ? $underTestActionUrl : $veActionUrl);
    }

    /**
     * @return AbstractRedirectActionResult
     */
    public function redirectToStartPage()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return new RedirectToRoute($this->startTestChangeService->getChangedValue(StartTestChangeService::URL)['url'],
                [
                    'id' => $this->context->getObfuscatedVehicleId(),
                    'noRegistration' => $this->startTestChangeService->getChangedValue(StartTestChangeService::NO_REGISTRATION)['noRegistration'],
                    'source' => $this->startTestChangeService->getChangedValue(StartTestChangeService::SOURCE)['source']
                ]);
        }

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
     * @return DvsaVehicle|DvlaVehicle
     */
    private function getVehicle()
    {
        return $this->context->getVehicle();
    }

    /**
     * @param bool $isValueChanged
     * @param DvsaVehicle $vehicle
     *
     * @return array
     */
    private function getDvsaVehicleClass($isValueChanged, DvsaVehicle $vehicle)
    {
        if ($isValueChanged) {
            return $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_CLASS);
        }

        return $vehicle->getVehicleClass()->getCode();
    }

    /**
     * @param $isValueChanged
     *
     * @return array
     */
    private function getDvlaVehicleClass($isValueChanged)
    {
        if ($isValueChanged) {
            return $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_CLASS);
        }

        return null;
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