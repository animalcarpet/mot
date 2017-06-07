<?php

namespace Vehicle\UpdateVehicleProperty\Process;

use Core\Action\RedirectToRoute;
use Core\Catalog\CountryOfRegistration\CountryOfRegistrationCatalog;
use Core\Routing\VehicleRouteList;
use Core\Routing\VehicleRoutes;
use Core\TwoStepForm\FormContextInterface;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\CountryOfRegistrationId;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\TypeCheck;
use DvsaMotTest\Service\StartTestChangeService;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\CountryOfRegistrationForm;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\View\Helper\Url;

class UpdateCountryOfRegistrationProcess implements UpdateVehicleInterface, AutoWireableInterface
{
    const PAGE_TITLE_UPDATE_DURING_TEST = "What is the vehicle's country of registration?";
    const PAGE_TITLE = 'Change country of registration';
    const CHANGE_UNDER_TEST_SUCCESSFUL_MESSAGE = 'You’ve changed the country of registration. This will save when you start a test';

    /** @var UpdateVehicleContext */
    private $context;

    private $countryCatalog;

    private $url;

    private $vehicleService;

    private $breadcrumbsBuilder;

    private $tertiaryTitleBuilder;

    /** @var StartTestChangeService */
    private $startTestChangeService;

    public function __construct(
        CountryOfRegistrationCatalog $countryCatalog,
        Url $url,
        VehicleService $vehicleService,
        VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder,
        StartTestChangeService $startTestChangeService
    ) {
        $this->countryCatalog = $countryCatalog;
        $this->url = $url;
        $this->vehicleService = $vehicleService;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->tertiaryTitleBuilder = new VehicleTertiaryTitleBuilder();
        $this->startTestChangeService = $startTestChangeService;
    }

    public function setContext(FormContextInterface $context)
    {
        TypeCheck::assertInstance($context, UpdateVehicleContext::class);
        $this->context = $context;
    }

    public function update($formData)
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            $this->startTestChangeService->saveChange(StartTestChangeService::CHANGE_COUNTRY, [
                    StartTestChangeService::CHANGE_COUNTRY => $formData[CountryOfRegistrationForm::FIELD_COUNTRY_OF_REGISTRATION
                ],
            ]);
            $this->startTestChangeService->updateChangedValueStatus(StartTestChangeService::CHANGE_COUNTRY, true);
        } else {
            $updateRequest = new UpdateDvsaVehicleRequest();
            $updateRequest->setCountryOfRegistrationId($formData['country-of-registration']);
            $this->vehicleService->updateDvsaVehicleAtVersion(
                $this->context->getVehicleId(),
                $this->context->getVehicle()->getVersion(),
                $updateRequest
            );
        }
    }

    public function getPrePopulatedData()
    {
        $countryOfRegistrationId = $this->getCountryOfRegistration();

        return [
            'country-of-registration' => $countryOfRegistrationId,
        ];
    }

    public function getSubmitButtonText()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return 'Continue';
        }

        return self::PAGE_TITLE;
    }

    public function getBreadcrumbs(MotAuthorisationServiceInterface $authorisationService)
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return $this->breadcrumbsBuilder->getChangeVehicleUnderTestBreadcrumbs($this->context->getObfuscatedVehicleId());
        }

        return $this->breadcrumbsBuilder->getVehicleEditBreadcrumbs(
            $this->getEditStepPageTitle(),
            $this->context->getObfuscatedVehicleId()
        );
    }

    public function createEmptyForm()
    {
        return new CountryOfRegistrationForm($this->countryCatalog->getAll());
    }

    public function getSuccessfulEditMessage()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return self::CHANGE_UNDER_TEST_SUCCESSFUL_MESSAGE;
        }

        return 'Country of registration has been successfully changed';
    }

    public function getEditStepPageTitle()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return self::PAGE_TITLE_UPDATE_DURING_TEST;
        }

        return self::PAGE_TITLE;
    }

    public function getPageSubTitle()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return self::PAGE_SUBTITLE_UPDATE_DURING_TEST;
        }

        return 'Vehicle';
    }

    public function buildEditStepViewModel($form)
    {
        $isChangeUnderTest = $this->context->isUpdateVehicleDuringTest();
        $formActionUrl = VehicleRoutes::of($this->url)->changeCountryOfRegistration($this->context->getObfuscatedVehicleId());
        $underTestActionUrl = VehicleRoutes::of($this->url)->changeUnderTestCountryOfRegistration($this->context->getObfuscatedVehicleId());
        $underTestBackUrl = $this->startTestChangeService->underTestReturnUrl($this->context->getObfuscatedVehicleId());
        $backUrl = $this->startTestChangeService->vehicleExaminerReturnUrl($this->context->getObfuscatedVehicleId());
        $tertiaryTitle = $this->getTertiaryTitleForVehicle();

        return (new UpdateVehiclePropertyViewModel())
            ->setForm($form)
            ->setSubmitButtonText($this->getSubmitButtonText())
            ->setPartial('partials/edit-country-country-of-registration')
            ->setBackUrl($isChangeUnderTest ? $underTestBackUrl : $backUrl)
            ->setBackLinkText($this->getBackButtonText())
            ->setFormActionUrl($isChangeUnderTest ? $underTestActionUrl : $formActionUrl)
            ->setPageTertiaryTitle($tertiaryTitle);
    }

    public function redirectToStartPage()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return new RedirectToRoute($this->startTestChangeService->getChangedValue(StartTestChangeService::URL)['url'],
                [
                    'id' => $this->context->getObfuscatedVehicleId(),
                    'noRegistration' => $this->startTestChangeService->getChangedValue(StartTestChangeService::NO_REGISTRATION)['noRegistration'],
                    'source' => $this->startTestChangeService->getChangedValue(StartTestChangeService::SOURCE)['source'],
                ]);
        }

        return new RedirectToRoute(VehicleRouteList::VEHICLE_DETAIL, ['id' => $this->context->getObfuscatedVehicleId()]);
    }

    /**
     * Says if the users is authorised to reach the page
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     *
     * @return bool
     */
    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService)
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            $vehicleClass = $this->context->getVehicle()->getVehicleClass();
            $vehicleClassCode = $vehicleClass ? $vehicleClass->getCode() : null;
            $isClassChangedInSession = $this->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_CLASS);
            $vehicleClass = $isClassChangedInSession ? $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_CLASS)[StartTestChangeService::CHANGE_CLASS] : $vehicleClassCode;
            if (!$this->startTestChangeService->isAuthorisedToTestClass($vehicleClass)) {
                return false;
            }
        }

        return $authorisationService->isGranted(PermissionInSystem::VEHICLE_UPDATE);
    }

    public function getEditPageLede()
    {
        return '';
    }

    /**
     * What should be displayed on the back button control.
     *
     * @return string
     */
    private function getBackButtonText()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            return 'Back';
        }

        return 'Cancel and return to vehicle';
    }

    private function getCountryOfRegistration()
    {
        if ($this->context->isUpdateVehicleDuringTest()) {
            $isCountryOfRegistrationChanged = $this->startTestChangeService->isValueChanged(StartTestChangeService::CHANGE_COUNTRY);

            if ($this->startTestChangeService->isDvlaVehicle() && !$isCountryOfRegistrationChanged) {
                return CountryOfRegistrationId::GB_UK_ENG_CYM_SCO_UK_GREAT_BRITAIN;
            }

            if ($isCountryOfRegistrationChanged) {
                return $this->startTestChangeService->getChangedValue(StartTestChangeService::CHANGE_COUNTRY)[StartTestChangeService::CHANGE_COUNTRY];
            }

            return $this->context->getVehicle()->getCountryOfRegistrationId();
        }

        return $this->context->getVehicle()->getCountryOfRegistrationId();
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
