<?php

namespace Vehicle\CreateVehicle\Action;

use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DateTime;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateTimeDisplayFormat;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Vehicle\CreateVehicle\Service\CreateVehicleModelService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\View\Model\ViewModel;

class ConfirmationAction
{
    private $authorisationService;
    private $createVehicleStepService;
    private $createVehicleModelService;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        CreateVehicleStepService $createVehicleStepService,
        CreateVehicleModelService $createVehicleModelService
    )
    {
        $this->authorisationService = $authorisationService;
        $this->createVehicleStepService = $createVehicleStepService;
        $this->createVehicleModelService = $createVehicleModelService;
    }

    public function execute()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::MOT_TEST_START);

        if (!$this->createVehicleStepService->isAllowedOnStep(CreateVehicleStepService::CONFIRM_STEP)) {
            return new RedirectToRoute(ReviewController::ROUTE);
        }

        $vehicle = $this->createVehicleStepService->getStep('review');

        $viewModel = (new ViewModel())
            ->setVariables([
                'makeAndModel' => $this->getVehicleMakeNameById() . ' ' . $this->getVehicleModelNameById(),
                'registration' => $this->createVehicleStepService->getStep('reg-vin')['reg-input'],
                'dateAndTime' => (new DateTime())->format(DateTimeDisplayFormat::FORMAT_DATETIME_SHORT),
                'testNumber' => $vehicle['startedMotTestNumber'],
            ]);

        return (new ViewActionResult())
            ->setTemplate('vehicle/create-vehicle/confirmation.twig')
            ->setViewModel($viewModel);
    }

    private function getVehicleMakeNameById()
    {
        $makeName = '';
        $vehicleMake = $this->createVehicleStepService->getStep('make')['vehicleMake'];

        if ($vehicleMake == 'Other') {
            return $this->createVehicleStepService->getStep('make')['Other'];
        }

        foreach ($this->createVehicleStepService->getStaticData()['make'] as $makes) {
            if ($makes['id'] == $this->createVehicleStepService->getStep('make')['vehicleMake']) {
                $makeName = $makes['name'];
            }
        }

        return $makeName;
    }

    private function getVehicleModelNameById()
    {
        $modelName = '';
        $vehicleModel = $this->createVehicleStepService->getStep('model')['vehicleModel'];

        if ($vehicleModel == 'Other') {
            return $this->createVehicleStepService->getStep('model')['Other'];
        }

        foreach ($this->createVehicleModelService->getModelFromMakeInSession() as $model) {
            if ($model['id'] == $vehicleModel) {
                $modelName = $model['name'];
            }
        }

        return $modelName;
    }
}