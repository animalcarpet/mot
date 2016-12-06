<?php

namespace Vehicle\CreateVehicle\Action;

use Core\Action\ViewActionResult;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\View\Model\ViewModel;

class StartAction
{
    private $authorisationService;
    private $createVehicleStepService;

    public function __construct(MotAuthorisationServiceInterface $authorisationService,
                                CreateVehicleStepService $createVehicleStepService)
    {
        $this->authorisationService = $authorisationService;
        $this->createVehicleStepService = $createVehicleStepService;
    }

    public function execute()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::MOT_TEST_START);
        $this->createVehicleStepService->loadStepsIntoSession();
        $result = new ViewActionResult();
        $viewModel = new ViewModel();
        $result->setTemplate('vehicle/create-vehicle/start.twig');

        $result->setViewModel($viewModel);

        return $result;
    }
}
