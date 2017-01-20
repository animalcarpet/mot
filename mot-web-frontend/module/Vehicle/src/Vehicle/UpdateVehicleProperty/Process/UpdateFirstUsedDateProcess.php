<?php

namespace Vehicle\UpdateVehicleProperty\Process;

use Core\Action\RedirectToRoute;
use Core\Routing\VehicleRoutes;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\TypeCheck;
use GuzzleHttp\Exception\ClientException;
use Vehicle\UpdateVehicleProperty\Context\UpdateVehicleContext;
use Vehicle\UpdateVehicleProperty\Form\FirstUsedDateForm;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleTertiaryTitleBuilder;
use Vehicle\UpdateVehicleProperty\ViewModel\UpdateVehiclePropertyViewModel;
use Zend\View\Helper\Url;

class UpdateFirstUsedDateProcess implements SingleStepProcessInterface, AutoWireableInterface
{
    /** @var UpdateVehicleContext */
    private $context;

    private $url;

    private $vehicleService;

    private $breadcrumbsBuilder;

    private $tertiaryTitleBuilder;

    public function __construct(
        Url $url,
        VehicleService $vehicleService,
        VehicleEditBreadcrumbsBuilder $breadcrumbsBuilder
    )
    {
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
        $date = (new \DateTime())->setDate(
            $formData[FirstUsedDateForm::FIELD_DATE_YEAR],
            $formData[FirstUsedDateForm::FIELD_DATE_MONTH],
            $formData[FirstUsedDateForm::FIELD_DATE_DAY]
        );
        $updateRequest->setFirstUsedDate($date);
        $this->vehicleService->updateDvsaVehicleAtVersion(
            $this->context->getVehicleId(),
            $this->context->getVehicle()->getVersion(),
            $updateRequest
        );
    }

    public function getPrePopulatedData()
    {
        $vehicle = $this->vehicleService->getDvsaVehicleById((int)$this->context->getVehicleId());
        if ($vehicle->getFirstUsedDate() !== null)
        {
            $date = new \DateTime($vehicle->getFirstUsedDate());
            return [
                FirstUsedDateForm::FIELD_DATE_DAY => $date->format('d'),
                FirstUsedDateForm::FIELD_DATE_MONTH => $date->format('m'),
                FirstUsedDateForm::FIELD_DATE_YEAR => $date->format('Y')
            ];
        }
        return [];
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
        return new FirstUsedDateForm();
    }

    public function getSuccessfulEditMessage()
    {
        return "Vehicle’s date of first use has been changed successfully";
    }

    public function getEditStepPageTitle()
    {
        return "Change date of first use";
    }

    public function getPageSubTitle()
    {
        return "Vehicle";
    }

    public function buildEditStepViewModel($form)
    {
        $formActionUrl = VehicleRoutes::of($this->url)
            ->changeFirstUsedDate($this->context->getObfuscatedVehicleId());

        $backUrl = VehicleRoutes::of($this->url)
            ->vehicleDetails($this->context->getObfuscatedVehicleId(), []);

        $tertiaryTitle = $this->tertiaryTitleBuilder->getTertiaryTitleForVehicle($this->context->getVehicle());

        return (new UpdateVehiclePropertyViewModel())
            ->setForm($form)
            ->setSubmitButtonText("Change date of first use")
            ->setPartial("partials/edit-first-used-date")
            ->setBackUrl($backUrl)
            ->setFormActionUrl($formActionUrl)
            ->setPageTertiaryTitle($tertiaryTitle);
    }

    public function redirectToStartPage()
    {
        return new RedirectToRoute("vehicle/detail",
            ['id' => $this->context->getObfuscatedVehicleId()]
        );
    }

    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService)
    {
        return $authorisationService->isGranted(PermissionInSystem::VEHICLE_CHANGE_PROPERTY_EXPANDED);
    }

    public function getEditPageLede()
    {
        return "";
    }
}