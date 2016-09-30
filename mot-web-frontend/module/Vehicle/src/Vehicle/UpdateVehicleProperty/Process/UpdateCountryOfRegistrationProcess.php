<?php

namespace Vehicle\UpdateVehicleProperty\Process;

use Core\Action\RedirectToRoute;
use Core\Catalog\CountryOfRegistration\CountryOfRegistrationCatalog;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\NotImplementedException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\TypeCheck;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\CountryOfRegistrationForm;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\View\Helper\Url;

class UpdateCountryOfRegistrationProcess implements SingleStepProcessInterface, AutoWireableInterface
{
    /** @var UpdateVehicleContext */
    private $context;

    private $countryCatalog;

    private $url;

    private $vehicleService;

    private $breadcrumbsBuilder;

    private $tertiaryTitleBuilder;

    public function __construct(
        CountryOfRegistrationCatalog $countryCatalog,
        Url $url,
        VehicleService $vehicleService,
        VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder
    )
    {
        $this->countryCatalog = $countryCatalog;
        $this->url = $url;
        $this->vehicleService = $vehicleService;
        $this->breadcrumbsBuilder = $breadcrumbsBuilder;
        $this->tertiaryTitleBuilder = new VehicleTertiaryTitleBuilder();
    }

    public function setContext(FormContextInterface $context)
    {
        TypeCheck::assertInstance($context, UpdateVehicleContext::class);
        $this->context = $context;
    }

    public function update($formData)
    {
        $updateRequest = new UpdateDvsaVehicleRequest();
        $updateRequest->setCountryOfRegistrationId($formData['country-of-registration']);
        $this->vehicleService->updateDvsaVehicle($this->context->getVehicleId(), $updateRequest);
    }

    public function getPrePopulatedData()
    {
        $vehicle = $this->vehicleService->getDvsaVehicleById((int)$this->context->getVehicleId());
        return ['country-of-registration' => $vehicle->getCountryOfRegistrationId()];
    }

    public function getSubmitButtonText()
    {
        return null;
    }

    public function getBreadcrumbs(MotAuthorisationServiceInterface $authorisationService)
    {
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
        return "Country of registration changed successfully";
    }

    public function getEditStepPageTitle()
    {
        return "Change country of registration";
    }

    public function getPageSubTitle()
    {
        return "Vehicle";
    }

    public function buildEditStepViewModel($form)
    {
        $formActionUrl = $this->url->__invoke(
            "vehicle/detail/change/country-of-registration",
            ['id' => $this->context->getObfuscatedVehicleId()]
        );

        $backUrl = $this->url->__invoke("vehicle/detail",
            ['id' => $this->context->getObfuscatedVehicleId()]
        );

        $tertiaryTitle = $this->tertiaryTitleBuilder->getTertiaryTitleForVehicle($this->context->getVehicle());

        return (new UpdateVehiclePropertyViewModel())
            ->setForm($form)
            ->setSubmitButtonText("Change country of registration")
            ->setPartial("partials/edit-country-country-of-registration")
            ->setBackUrl($backUrl)
            ->setFormActionUrl($formActionUrl)
            ->setPageTertiaryTitle($tertiaryTitle)
        ;
    }

    public function redirectToStartPage()
    {
        return new RedirectToRoute("vehicle/detail",
            ['id' => $this->context->getObfuscatedVehicleId()]
        );
    }

    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService)
    {
        return $authorisationService->isGranted(PermissionInSystem::VEHICLE_UPDATE);
    }

    public function getEditPageLede()
    {
        return "";
    }
}